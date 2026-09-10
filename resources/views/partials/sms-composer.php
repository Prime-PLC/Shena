<?php
// Reusable editor for a fixed recipient; all hidden state is server-issued.
$smsComposerId = (string)$smsComposerId;
?>
<section id="<?= htmlspecialchars($smsComposerId, ENT_QUOTES) ?>" class="sms-review-composer" tabindex="-1">
    <h3><?= htmlspecialchars($smsTitle ?? 'Review the SMS', ENT_QUOTES) ?></h3>
    <form method="POST" action="<?= htmlspecialchars($smsAction, ENT_QUOTES) ?>" data-sms-composer>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
        <?php foreach (($smsHidden ?? []) as $key => $value): ?>
            <input type="hidden" name="<?= htmlspecialchars($key, ENT_QUOTES) ?>" value="<?= htmlspecialchars((string)$value, ENT_QUOTES) ?>">
        <?php endforeach; ?>
        <div class="sms-review-layout">
            <div>
                <p>To: <strong><?= htmlspecialchars($smsPhone, ENT_QUOTES) ?></strong></p>
                <label for="<?= htmlspecialchars($smsComposerId, ENT_QUOTES) ?>-message">Message to the recipient</label>
                <textarea id="<?= htmlspecialchars($smsComposerId, ENT_QUOTES) ?>-message" name="message" rows="8" maxlength="1000" required data-sms-message aria-describedby="<?= htmlspecialchars($smsComposerId, ENT_QUOTES) ?>-count"><?= htmlspecialchars($smsMessage, ENT_QUOTES, 'UTF-8') ?></textarea>
                <p id="<?= htmlspecialchars($smsComposerId, ENT_QUOTES) ?>-count" data-sms-count aria-live="polite"></p>
                <p class="sms-review-help">Check the recipient, cover details and amount before sending. Editing this message does not change the saved account.</p>
                <div class="sms-review-actions">
                    <button type="submit" name="decision" value="send" class="btn btn-primary"><i class="fas fa-paper-plane" aria-hidden="true"></i> Review and send SMS</button>
                    <?php if (!empty($smsAllowSave)): ?>
                        <button type="submit" name="decision" value="save" class="btn btn-secondary">Save draft</button>
                        <button type="submit" name="decision" value="discard" class="btn btn-outline-secondary" formnovalidate>Discard SMS</button>
                    <?php endif; ?>
                </div>
            </div>
            <details class="sms-review-preview" open>
                <summary>Mobile SMS preview</summary>
                <div class="sms-phone-frame">
                    <div class="sms-phone-sender">SHENA</div>
                    <div class="sms-bubble" data-sms-bubble><?= htmlspecialchars($smsMessage, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </details>
        </div>
    </form>
</section>
