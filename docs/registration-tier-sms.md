# Registration tier and SMS delivery

Public, agent and admin registration require an explicit Basic or Platinum choice. The picker shows the selected product's prices. Invalid/missing tier, package, DOB or Platinum pricing is rejected instead of silently registering Basic. Legacy registration URLs now use the current handler.

Corporate members always start on Basic, whether the principal registers as Basic or Platinum. Only the principal receives a Platinum registration selection. A separate admin conversion is required to move a corporate member to Platinum; that conversion retains its existing DOB and maturity checks. Public registration does not accept a corporate count without person details; additional groups use the staff form.

New Platinum principal registrations save pending approval coverage with `registration_selected = 1`. Billing and account SMS use its Platinum rate immediately, replacing the corresponding Basic rate. Cover approval and maturity restrictions still apply. Existing pending conversions keep their previous billing behavior. This does not repair historical mismatches automatically.

## SMS behavior

| Origin | Delivery |
| --- | --- |
| Authenticated manager/super-admin POST to an admin action, or agent POST to an agent action | Review draft associated with that staff user |
| Member/public action, payment callback, or background process | Automatic notification; no staff review draft |
| Authentication code or existing payment receipt | Existing direct delivery behavior |
| Explicitly reviewed draft or scheduled campaign | Existing queue/delivery behavior |

Draft creation independently checks server session identity, HTTP method and staff route. Posted context cannot impersonate a staff actor. Existing controller authorization and CSRF checks remain in force.

The staff registration invitation and registration-payment activation share one durable welcome event per member. A member-row lock and primary key serialize competing paths. The welcome names the actual tier and total contribution, includes a staff invitation link when appropriate, and explains Platinum approval/waiting restrictions. The existing date-limited Customer Service Week appreciation is retained.

Staff invitations are recorded inside the registration transaction. Automatic welcomes persist to the SMS queue and event table together; delivery happens only after commit. A nested payment transaction leaves its queued message to the existing SMS worker. Rollbacks remove the event and queue item together. Discarding a reviewed welcome does not cause payment activation to recreate it.

## Deployment

1. Back up the database and pause registration/payment workers briefly.
2. Apply `database/migrations/030_registration_tier_sms.sql` once, before deploying these files. Requires the existing Platinum, SMS review and queue migrations.
3. Deploy, then resume the existing SMS queue worker. No new scheduled job is introduced.
4. In staging, register Basic and Platinum through all three forms, including additional groups. Check the monthly total, approval restrictions, review draft ownership and payment activation without another welcome.

The migration baselines all existing member IDs so reactivation does not send historical welcomes again. Existing drafts and queue entries are preserved, not replayed or automatically sent. Review any older member/system-created drafts separately; this change prevents new ones.

Validation uses isolated in-memory persistence and a captured SMS transport, plus the existing PHP suite and JavaScript picker tests. A live database migration, gateway delivery and signed-in browser smoke test are deployment checks, not claimed as completed here.
