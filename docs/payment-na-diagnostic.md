# Payment N/A diagnostic

After deploying this PR to staging, sign in as an active super_admin or manager
and open `/public/payment-na-diagnostic.php` on the same host. A CSV downloads.
No terminal command, migration, or separate credentials are needed.

By default it exports current active contribution-paying members classified as
defaulted whose last qualifying contribution date is NULL, matching the report.
Use `?member_number=SH-123456` to inspect one member even if their N/A has resolved.
Replace the example number with the actual member number.

The script uses the deployed PaymentStatusService for the snapshot and a MySQL
read-only transaction for all database work. It does not repair records, change
classification rules, allocate payments, or send messages. The snapshot uses
today in Africa/Nairobi; it does not reproduce a historical export exactly.
Current-month allocated amounts and transactions dated this month are separate
columns because advance payments can cover later months.

The explanation includes overlapping evidence about incomplete payments,
excluded completed types (including Platinum), and missing dates. NULL dates
fall back to created_at; empty-string dates follow the current report behavior
and do not fall back. Type/status counts cover all linked payment history.
No linked payments means none linked by internal member ID; it does not prove
the member never paid. Payments linked to other members or unreconciled receipts
require a separate reconciliation review. The script does not guess ownership
from shared phone numbers.

An export with only a header means there are no matching defaulted N/A members.
A filtered member absent from the report's active contribution-paying population
returns 404. Other failures return an error rather than a partial CSV.
Treat downloaded CSVs as private member records. The endpoint checks both session
role and the current database role/status and sends no-store headers.

Validation: `php -l public/payment-na-diagnostic.php` and
`php tests/payment_na_diagnostic_test.php`. On staging, confirm signed-out/member
requests are denied and compare an N/A and a paid member with their payment
history before using the output for reconciliation.
