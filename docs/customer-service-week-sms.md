# Customer Service Week SMS (2026)

New messages generated from 5 October 00:00 through 9 October 23:59:59, Africa/Nairobi, use this short non-promotional appreciation:

> Happy Customer Service Week! Thank you for trusting Shena Companion. We value you.

## Affected messages

| Message | Change | Delivery behavior |
| --- | --- | --- |
| Payment confirmation | Replace `Thank you. - Shena Companion` with the appreciation. Keep amount and transaction ID. Covers the existing PaymentService and PaymentReconciliationService receipt callers. | Existing immediate transactional send. |
| Registration-payment welcome | Append the appreciation to the existing personalised welcome. Retain name, monthly amount, deadline, paybill, account and member number. | Existing SMS review draft. |
| Generic membership welcome | Append appreciation to existing welcome/member number text. | Existing review draft; helper currently has no in-repository callers. |
| Generic membership activation | Append appreciation to existing activation/member number/sign-in text. | Existing review draft; helper currently has no in-repository callers. |

Example receipt (149 ASCII characters with these example values):

> Payment confirmed! KES 600.0 received. Transaction ID: TEST123456. Happy Customer Service Week! Thank you for trusting Shena Companion. We value you.

Existing reminders, grace/suspension notices, claims, hospital messages, OTP/reset codes, account-invitation links, upgrade messages, admin/provider alerts and custom/bulk campaigns are unchanged. No standalone appreciation campaign or additional send is introduced. Previously generated drafts/queued messages are not edited. Drafts created during the week retain their text; review/remove the greeting if sending them after 9 October.

The regular wording returns automatically for messages generated from 10 October. This is a one-off 2026 window, not an annually recurring greeting. Existing lengthy welcomes and receipts with long field values may use multiple SMS segments; no transaction, payment or membership details are truncated. No schema migration or scheduler is needed. No messages are sent by the tests.
