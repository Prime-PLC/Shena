# Basic / Platinum conversion verification

Run from the repository root:

```powershell
php tests/platinum_conversion_edge_cases.php
php tests/account_change_notification_test.php
php tests/sms_queue_claim_test.php
node tests/persistent_feedback_test.js
```

The conversion suite exercises the actual admin controller and pricing service against synthetic member/corporate rows and an in-memory transactional database. It never loads application credentials, touches a real database, or sends an SMS. Exit 1 now indicates a regression, rather than the earlier diagnostic suite's unresolved expectations.

Coverage includes all 16 packages on both sides of their age boundaries for principal and corporate owners; missing/invalid/future/relative DOBs; age 59/60 maturity; absent/null/blank/legacy/conflicting package fields; stale stored amounts; invalid owners; admin and CSRF checks; stale quote previews; inactive accounts; duplicate coverages; repeat activation/reversal; cancelled coverage reactivation; audit failure rollback; and synthetic reproductions of the backup mismatches.

The new contract is exact package pricing. Owner DOB must be a real calendar date for an adult aged 18â€“100 and determines maturity, but cannot select a different package price band. Cached package names are display data; exact conflicting package keys require review. Contact-only edits and repeat activation do not automatically repair historical amounts.

## Admin workflow

1. Save the correct Basic package for the principal and any additional members.
2. Review the Platinum conversion amount and confirm. Reload is required if the quote changed since the page was loaded.
3. Finish corrections. Conversion, reversal and contribution edits do not automatically send SMS.
4. Review the final message under **Notify the member**, including recipient and total, then send.
5. The account update is recorded in the existing SMS queue before provider submission. Check SMS history for delivery confirmation.

Identical latest notifications are blocked; changed notifications are blocked while an earlier update is pending or within a 10-minute cooldown. A later legitimate return to an earlier state can be notified. Failed submissions can be reviewed and retried; uncertain outcomes must be checked in provider history. An atomic pending-to-processing claim prevents overlapping workers from submitting the same queue item twice.

Shared flash feedback remains visible until dismissed, combines multiple results, uses text-only rendering and restores focus. An inline fallback remains when JavaScript is unavailable.

No historical data repair or database migration was executed. The existing legacy conversion SQL now uses exact package tariffs if it is deliberately run in the future; it is not a repair script for existing records. The stored-record review from the backups remains a separate task.

Limits: tests use storage and DOM fixtures, not live MySQL concurrency, a rendered browser session, production or the real SMS provider. No actual SMS was sent during verification. A worker crash after claiming a queue row can leave it processing; check provider outcome before manually retrying rather than automatically resending an uncertain submission.


## Shared SMS review deployment

Apply `database/migrations/025_sms_review_drafts.sql` before deploying the updated code. It creates only the draft table; it does not repair or rewrite existing member, coverage or SMS history records. This migration has not been run by the test suite.

Business events now save SMS drafts. Staff can edit, preview, save, discard or explicitly send them through `/sms-review`; account conversions use the member page's live account editor. Authentication codes and explicitly approved campaigns still use immediate delivery. Background business notifications appear in the admin review inbox. Draft creation failure never falls back to automatic SMS delivery.

Run `php tests/sms_review_test.php` for draft lifecycle, ownership, stale preview, duplicate-send and age-boundary checks. Age bands are 18–70, 71–80, 81–90 and 91–100; the old parents `70_80` key is accepted as a read-compatible alias for `71_80`.

Before production rollout, verify the migration against a disposable MySQL copy and browser-test redirect/AJAX feedback, keyboard dismissal and the mobile composer. The isolated tests do not validate real MySQL locking, provider delivery or browser layout. Do not run the historical repair SQL as part of this code deployment.

Final release checks: all 30 isolated PHP test scripts, changed PHP syntax, JavaScript syntax and persistent-feedback fixtures pass. Migration 025 is registered in the deployment runner. The local MySQL client cannot load caching_sha2_password, so live database and browser/provider validation remain deployment checks. No production migration or SMS delivery was performed.
