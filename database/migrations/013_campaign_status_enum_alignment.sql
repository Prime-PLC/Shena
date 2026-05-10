-- Align campaign status enums with the admin campaign controllers.
-- Safe to run once on production before using cancel/pause campaign actions.

ALTER TABLE bulk_messages
  MODIFY status ENUM('draft','scheduled','sending','paused','completed','failed','cancelled') DEFAULT 'draft';

ALTER TABLE bulk_message_recipients
  MODIFY status ENUM('pending','sent','failed','bounced','skipped') DEFAULT 'pending';

ALTER TABLE bulk_message_recipients
  ADD COLUMN provider_message_id VARCHAR(100) NULL AFTER delivery_method,
  ADD COLUMN provider_response TEXT NULL AFTER provider_message_id;
