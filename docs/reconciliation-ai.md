# Payment Reconciliation: Integrity Rules and AI-Assisted Matching

Applies to migration `031_reconciliation_integrity_and_ai_suggestions.sql`.

## 1. Principles

1. **Money is posted only by deterministic code.** The AI never creates a payment, extends coverage or changes a member.
2. **Weak evidence goes to a human.** Phone-only matches and amount mismatches are queued as `unmatched` with a written reason.
3. **Every step is atomic and auditable.** Posting, side-effects, the audit row and the callback link commit together or not at all.
4. **AI output is untrusted.** It is schema-validated, limited to the candidates it was offered, and its confidence is capped by facts computed locally.

## 2. Data flow (C2B callback)

```mermaid
flowchart TD
    A[M-Pesa Paybill callback] --> B[mpesa-c2b-callback.php<br/>IP allowlist + raw log]
    B --> C{TransID already seen?}
    C -- "payment exists" --> D[Return duplicate]
    C -- "stored but no payment" --> E[Retry processing]
    C -- "new" --> F[Store raw callback]
    E --> G
    F --> G[autoReconcilePayment]
    G --> H{Member found by<br/>ID number or member number?}
    H -- no --> U
    H -- "phone only" --> U
    H -- yes --> I{Amount equals expected<br/>for payment type?}
    I -- no --> U[(payments: unmatched<br/>+ reason in notes)]
    I -- yes --> T[[BEGIN TRANSACTION]]
    T --> P[Create payment: matched]
    P --> S[Extend coverage / activate / reactivate]
    S --> L[Write reconciliation log]
    L --> K[Mark callback processed]
    K --> M[[COMMIT]]
    M --> N[Send confirmation SMS]
    U --> Q[Admin: Unmatched Payments page]
```

A failure anywhere inside the transaction rolls everything back. The raw callback stays `processed = 0` with `processing_error` set, and `cron/retry_unprocessed_c2b.php` re-attempts it (up to 5 times).

## 3. AI-assisted suggestion flow

```mermaid
sequenceDiagram
    actor Admin
    participant UI as payments-unmatched.php
    participant C as PaymentController
    participant AI as AiMatchingService
    participant DB as MySQL
    participant LLM as Anthropic API

    Admin->>UI: Click "AI suggest"
    UI->>C: POST /admin/payments/{id}/ai-suggest
    C->>AI: suggest(paymentId)
    AI->>DB: Load payment, members (local typo / phone / name features)
    AI->>LLM: payment (masked ref) + candidates c1..cN, features, names only
    LLM-->>AI: JSON {best_candidate, confidence, rationale}
    AI->>AI: Validate keys, cap confidence, store suggestion
    AI-->>UI: Suggested member + rationale (advisory)
    Admin->>UI: Use suggestion (adds note)
    UI->>C: POST /admin/payments/reconcile {ai_suggestion_id}
    C->>DB: manualReconciliation (transactional, audited as ai_assisted)
```

### What the model receives

| Sent | Never sent |
|---|---|
| Amount, sender name, **masked** account reference (last 3 chars) | National ID numbers |
| Candidate key (`c1`...), first/last name, status | Phone numbers, database ids |
| Local features: edit distance, phone-match flag, shared name tokens, amount-matches-expected | Email, address, payment history |

### Guardrails

- Unknown candidate key in the reply is discarded, so the model cannot introduce a member.
- Non-JSON or malformed output fails closed to "no suggestion".
- Confidence is capped at 85. If the chosen candidate has no hard evidence (account distance over 2, no phone match, no shared name token) it is capped at 30. This limits prompt injection through the payer-controlled sender name.
- Suggestions are cached for 10 minutes per payment to limit API cost.

## 4. Data model

```mermaid
erDiagram
    MPESA_C2B_CALLBACKS ||--o| PAYMENTS : "payment_id"
    PAYMENTS }o--o| MEMBERS : "member_id (NULL while unmatched)"
    PAYMENTS ||--o{ PAYMENT_RECONCILIATION_LOG : "audit trail"
    PAYMENTS ||--o{ PAYMENT_AI_SUGGESTIONS : "advisory only"
    MEMBERS ||--o{ PAYMENT_AI_SUGGESTIONS : "suggested_member_id"
    USERS ||--o{ PAYMENT_AI_SUGGESTIONS : "requested_by / resolved_by"

    MPESA_C2B_CALLBACKS {
        int id PK
        string trans_id UK
        bool processed
        int attempts
        text processing_error
        int payment_id FK
    }
    PAYMENTS {
        int id PK
        string mpesa_receipt_number UK "new unique index"
        enum reconciliation_status
        text reconciliation_notes
    }
    PAYMENT_AI_SUGGESTIONS {
        int id PK
        int payment_id FK
        int suggested_member_id FK
        decimal confidence
        text rationale
        enum status "suggested accepted rejected error"
    }
```

## 5. Use cases

```mermaid
flowchart LR
    Payer((Payer))
    Admin((Admin / Manager))
    Cron((Cron))

    Payer --> U1[Pay via Paybill]
    U1 --> U2[Auto-reconcile]
    Admin --> U3[Review unmatched queue]
    Admin --> U4[Find matches rule-based]
    Admin --> U5[AI suggest]
    Admin --> U6[Confirm / dismiss suggestion]
    Admin --> U7[Search member manually]
    Cron --> U8[Retry unprocessed callbacks]
    U5 -.advisory.-> U6
```

## 6. Configuration

```
AI_RECONCILIATION_ENABLED=true     # off by default
ANTHROPIC_API_KEY=...              # server-side only, never committed
AI_RECONCILIATION_MODEL=claude-sonnet-5-5
```

With the flag off or no key, the "AI suggest" button reports that suggestions are not enabled and everything else behaves normally.

## 7. Deployment checklist

1. Run the **preflight query** at the top of migration 031 and resolve any duplicate receipts.
2. Apply the migration.
3. Add the cron entry: `*/10 * * * * php /path/to/Shena/cron/retry_unprocessed_c2b.php`
4. Set the `.env` values only when ready to enable AI.
5. Run `php tests/reconciliation_logic_test.php`.

## 8. Known limits and follow-ups

- Amount rule: `monthly` payments must equal `members.monthly_contribution`. Members with no contribution on file are still auto-posted (flagged as unknown) to avoid flooding the queue. Multi-month payments now go to review instead of silently granting one month.
- The STK push path and `PaymentController` still apply membership side-effects separately; consolidating them into this service is a recommended next step.
- New admin JSON endpoints follow the existing pattern (session role check, no CSRF token). Adding CSRF to all admin JSON endpoints is a separate hardening task.
- Statement-upload reconciliation (diff against the M-Pesa statement to find callbacks that never arrived) is not in this PR.
