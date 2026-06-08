-- SMS delivery lifecycle alignment for HostPinnacle DLR tracking.
-- Run after 013_campaign_status_enum_alignment.sql.

ALTER TABLE bulk_messages
  MODIFY status ENUM(
    'draft',
    'scheduled',
    'sending',
    'submitted',
    'partially_delivered',
    'completed',
    'failed',
    'cancelled',
    'paused'
  ) DEFAULT 'draft';

ALTER TABLE bulk_messages
  ADD COLUMN submitted_count INT DEFAULT 0 AFTER sent_count,
  ADD COLUMN delivered_count INT DEFAULT 0 AFTER submitted_count,
  ADD COLUMN undelivered_count INT DEFAULT 0 AFTER delivered_count,
  ADD COLUMN dlr_synced_at DATETIME NULL AFTER completed_at;

ALTER TABLE bulk_message_recipients
  MODIFY status ENUM(
    'pending',
    'submitted',
    'sent',
    'delivered',
    'failed',
    'undelivered',
    'expired',
    'rejected',
    'unknown',
    'bounced',
    'skipped'
  ) DEFAULT 'pending';

ALTER TABLE bulk_message_recipients
  ADD COLUMN provider_status VARCHAR(80) NULL AFTER provider_message_id,
  ADD COLUMN provider_cause VARCHAR(255) NULL AFTER provider_status,
  ADD COLUMN submitted_at DATETIME NULL AFTER sent_at,
  ADD COLUMN delivered_at DATETIME NULL AFTER submitted_at,
  ADD COLUMN dlr_checked_at DATETIME NULL AFTER delivered_at,
  ADD COLUMN dlr_attempts INT DEFAULT 0 AFTER dlr_checked_at;

ALTER TABLE sms_queue
  MODIFY status ENUM(
    'pending',
    'processing',
    'submitted',
    'sent',
    'delivered',
    'failed',
    'undelivered',
    'expired',
    'rejected',
    'unknown'
  ) DEFAULT 'pending';

ALTER TABLE sms_queue
  ADD COLUMN provider_message_id VARCHAR(100) NULL AFTER error_message,
  ADD COLUMN provider_status VARCHAR(80) NULL AFTER provider_message_id,
  ADD COLUMN provider_cause VARCHAR(255) NULL AFTER provider_status,
  ADD COLUMN provider_response TEXT NULL AFTER provider_cause,
  ADD COLUMN submitted_at DATETIME NULL AFTER provider_response,
  ADD COLUMN delivered_at DATETIME NULL AFTER submitted_at,
  ADD COLUMN dlr_checked_at DATETIME NULL AFTER delivered_at,
  ADD COLUMN dlr_attempts INT DEFAULT 0 AFTER dlr_checked_at;

CREATE INDEX idx_sms_recipients_provider_message
  ON bulk_message_recipients(provider_message_id);

CREATE INDEX idx_sms_recipients_dlr
  ON bulk_message_recipients(status, dlr_checked_at);

CREATE INDEX idx_sms_queue_provider_message
  ON sms_queue(provider_message_id);

CREATE INDEX idx_sms_queue_dlr
  ON sms_queue(status, dlr_checked_at);
