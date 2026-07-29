-- Add recipient-level processing locks so concurrent SMS campaign workers cannot
-- submit the same pending recipient at the same time.

ALTER TABLE bulk_message_recipients
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
    'unknown',
    'bounced',
    'skipped'
  ) DEFAULT 'pending';

ALTER TABLE bulk_message_recipients
  ADD COLUMN processing_token VARCHAR(64) NULL AFTER error_message,
  ADD COLUMN processing_started_at DATETIME NULL AFTER processing_token;

CREATE INDEX idx_sms_recipients_processing_token
  ON bulk_message_recipients(processing_token);
