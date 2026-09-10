<?php

/** Business events create review drafts. Only an explicit staff review queues delivery. */
class SmsReviewService
{
    public static function validateMessage(string $message): string
    {
        $message = trim(str_replace(["\r\n", "\r"], "\n", $message));
        $length = preg_match_all('/./us', $message);
        if ($length === false || $length < 1 || $length > 1000 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $message)) {
            throw new RuntimeException('Enter an SMS between 1 and 1000 characters.');
        }
        return $message;
    }

    public static function phone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (preg_match('/^0([17]\d{8})$/', $phone, $m)) $phone = '254' . $m[1];
        if (preg_match('/^[17]\d{8}$/', $phone)) $phone = '254' . $phone;
        if (!preg_match('/^254[17]\d{8}$/', $phone)) throw new RuntimeException('A valid Kenyan recipient number is required.');
        return $phone;
    }

    public function create(string $phone, string $message, array $context = []): int
    {
        $db = Database::getInstance();
        $phone = self::phone($phone);
        $message = self::validateMessage($message);
        $actor = in_array($_SESSION['user_role'] ?? '', ['super_admin', 'manager', 'agent'], true) ? (int)$_SESSION['user_id'] : null;
        $source = substr((string)($context['source'] ?? 'Business notification'), 0, 160);
        // Explicit event keys can replace an unsent correction; otherwise only exact duplicates merge.
        $key = hash('sha256', $phone . '|' . ($actor ?? 'system') . '|' . ($context['key'] ?? hash('sha256', $message)));
        $db->query("INSERT INTO sms_review_drafts (phone_number, message, source, open_key, created_by) VALUES (:phone, :message, :source, :open_key, :actor)
            ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), version = IF(message <> VALUES(message), version + 1, version), message = VALUES(message), source = VALUES(source), updated_at = NOW()",
            ['phone' => $phone, 'message' => $message, 'source' => $source, 'open_key' => $key, 'actor' => $actor]);
        $id = (int)$db->getConnection()->lastInsertId();
        if ($actor) {
            $_SESSION['sms_review_ids'][$id] = $id;
            if (!headers_sent()) header('X-Shena-Sms-Review: /sms-review?draft=' . $id . '#sms-review-' . $id);
            $_SESSION['sms_feedback_target'] = '#sms-review-' . $id;
            $_SESSION['info'] = 'Your action is saved. An SMS draft is ready for you to edit and review. No SMS has been sent.';
        }
        return $id;
    }

    public static function token(array $draft): string
    {
        return hash('sha256', json_encode([$draft['id'], $draft['version'], $draft['phone_number'], $draft['message']]));
    }

    public function pending(int $actor, bool $admin, array $ids = [], int $page = 1): array
    {
        $params = [];
        $where = "status = 'draft'";
        if (!$admin) { $where .= ' AND created_by = :actor'; $params['actor'] = $actor; }
        if ($ids) {
            $holders = [];
            foreach (array_slice(array_unique(array_map('intval', $ids)), 0, 100) as $i => $id) { $holders[] = ':id' . $i; $params['id' . $i] = $id; }
            $where .= ' AND id IN (' . implode(',', $holders) . ')';
        }
        $offset = (max(1, $page) - 1) * 25;
        return Database::getInstance()->fetchAll("SELECT * FROM sms_review_drafts WHERE {$where} ORDER BY id DESC LIMIT 26 OFFSET {$offset}", $params);
    }

    /** Caller holds a transaction; the draft lock serializes double submissions. */
    public function review(int $id, int $actor, bool $admin, string $token, string $message, string $decision): ?int
    {
        $db = Database::getInstance();
        $candidate = $db->fetch('SELECT * FROM sms_review_drafts WHERE id = :id', ['id' => $id]);
        if (!$candidate || (!$admin && (int)$candidate['created_by'] !== $actor)) throw new RuntimeException('This SMS draft is not available to you.');
        if ($decision === 'send' && str_starts_with((string)($candidate['source'] ?? ''), 'Service provider: ')) {
            require_once __DIR__ . '/../models/ServiceProvider.php';
            (new ServiceProvider())->assertCurrentDraft($id);
        }
        // Acquire recipient locks in the same order before locking an individual draft.
        $db->fetchAll('SELECT id FROM sms_review_drafts WHERE phone_number = :phone ORDER BY id FOR UPDATE', ['phone' => $candidate['phone_number']]);
        $draft = $db->fetch('SELECT * FROM sms_review_drafts WHERE id = :id FOR UPDATE', ['id' => $id]);
        if (!$draft || (!$admin && (int)$draft['created_by'] !== $actor)) throw new RuntimeException('This SMS draft is not available to you.');
        if ($draft['status'] !== 'draft') throw new RuntimeException('This SMS was already reviewed. Nothing was sent again.');
        if (!hash_equals(self::token($draft), $token)) throw new RuntimeException('This draft changed. Review the latest message before continuing.');
        if (!in_array($decision, ['save', 'send', 'discard'], true)) throw new RuntimeException('Choose save, send or discard.');
        if ($decision === 'discard') {
            $db->update('sms_review_drafts', ['status' => 'discarded', 'open_key' => null, 'reviewed_by' => $actor, 'reviewed_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
            return null;
        }
        $message = self::validateMessage($message);
        if ($decision === 'save') {
            $db->update('sms_review_drafts', ['message' => $message, 'version' => (int)$draft['version'] + 1], 'id = :id', ['id' => $id]);
            return null;
        }
        $duplicate = $db->fetch("SELECT id FROM sms_queue WHERE phone_number = :phone AND message = :message AND status <> 'failed' AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE) LIMIT 1 FOR UPDATE", ['phone' => $draft['phone_number'], 'message' => $message]);
        if ($duplicate) throw new RuntimeException('This message was recently queued or submitted. Nothing was sent again.');
        $queueId = (int)$db->insert('sms_queue', ['phone_number' => $draft['phone_number'], 'message' => $message, 'priority' => 'normal', 'status' => 'pending']);
        $db->update('sms_review_drafts', ['message' => $message, 'status' => 'queued', 'open_key' => null, 'sms_queue_id' => $queueId, 'reviewed_by' => $actor, 'reviewed_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
        return $queueId;
    }
}
