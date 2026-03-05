# Epic Issue: Public Website + Member Portal Harmonization (Supervisor/Reviewer Level)

## Summary
This issue consolidates a coordinated UX/content + workflow harmonization across the public site and member portal while preserving existing business logic and design language.

Primary goals:
1. Content/branding consistency across all pages.
2. Minimal-friction registration while preserving current validation/payment logic.
3. Uniform package/pricing presentation with shared components.
4. Reliable member lifecycle behavior (registration, login, upgrade, logout).
5. Consistent support/help affordances across public/member views.

---

## Context Verified in Current Codebase
The following findings are confirmed from the current implementation and are the baseline for this issue:

- Public header still labels services as **Welfare Services**.
- Public footer and several public/member pages still contain **Kisumu**-specific wording.
- Floating support/help icon is currently implemented on the contact page only.
- Public package/pricing UI is page-specific and not componentized for reuse.
- Public registration and legacy registration flows coexist; member-number generation is inconsistent across flows.
- Logout behavior diverges by role (e.g., admin/member redirect to login pages instead of public home).
- Member upgrade logic depends on normalized package tiers, but stored package values are inconsistent in some creation paths.

---

## Business Requirements (Consolidated)

### A) Public Website
1. Replace/refresh imagery where requested.
2. Replace any location wording that implies "Kisumu-only" to regional/Kenya-wide wording where appropriate.
3. Harmonize registration flow into a minimal SHA-style form (fewer fields) while preserving existing registration logic and visual language.
4. Add a dedicated gallery page.
5. Streamline package display according to approved poster package matrix.
6. Replace favicon with a non-transparent logo image.
7. Pricing cards:
   - Use a shared, uniform pricing-card component across the website.
   - "View details" opens a minimal detail window/modal for each plan.
   - Add compare-plans section below pricing cards.
8. Services page/header updates:
   - Header label should read **Services** (not Welfare Services).
   - Add **Cash Benefit** under services.
9. Home CTA copy update:
   - "Talk to an Agent" -> "Talk to Us".
10. Add policy booklet, privacy policy, and T&Cs links across relevant pages.
11. Floating help icon should appear on all public pages.
12. Add/activate appropriate social links.
13. Update auto-generated member number format to align with Excel sheet format.
14. Remove conditional login blocker after account creation (self/admin/agent registrations):
   - Member should be able to login immediately using registered credentials.

### B) Member Portal
1. Fix plan-upgrade "required ID" issue in upgrade flow.
2. On contributions page, explicitly show stipulated monthly contribution amount.
3. Add "Need help?" floating chat affordance across all member views.
4. After logout (member/agent/admin), redirect to public home with success flash.

---

## Scope Decomposition (Engineering)

### Workstream 1: Global Content + Branding Consistency
- Replace Kisumu-specific copy where not intentionally location-specific.
- Update service naming to "Services" globally.
- Replace CTA copy on homepage.
- Introduce/standardize policy/privacy/T&Cs links in shared layouts.
- Wire real social profile URLs in shared footer/layout blocks.

### Workstream 2: Shared UI Components
- Build shared pricing-card partial/component used by membership/public pages.
- Add reusable plan-details modal/window behavior.
- Add compare-plans block below pricing grid.
- Extract floating help icon into shared layout partial for public pages.
- Add equivalent reusable help/chat widget in member layouts.

### Workstream 3: Registration Flow Harmonization
- Align active registration endpoints/views to one minimal UX entry path.
- Keep backend validation + package eligibility + payment behavior unchanged.
- Preserve CSRF, sanitization, uniqueness checks, and package age validation.
- Rationalize old/legacy registration endpoints to avoid divergent behavior.

### Workstream 4: Member Identity + Lifecycle Reliability
- Standardize member number generation through a single canonical formatter.
- Ensure all registration channels (public/admin/agent) use same format utility.
- Remove account-status conditional blocker for first login after successful registration.
- Keep policy/payment status messaging but do not block credential login.

### Workstream 5: Upgrade + Contributions + Logout Fixes
- Fix package tier normalization in upgrade flow to prevent required-ID/invalid-tier failures.
- Ensure contributions page clearly displays "Your Monthly Contribution: KES X".
- Standardize logout redirect for member/agent/admin to public home with success message.

---

## Confirmed Technical Risk/Root-Cause Notes

1. **Member number format inconsistency**
   - Different registration channels currently generate different formats (e.g., SC..., ADM..., SH-..., SCA...).
   - This risks duplicates, search/report inconsistencies, and business process mismatch with Excel standard.

2. **Upgrade flow package normalization mismatch**
   - Upgrade service expects normalized tiers (individual/couple/family/executive).
   - Some member creation paths persist non-normalized package keys, causing upgrade validation failures.

3. **Registration/login status gating**
   - Member login currently conditionally redirects inactive/pending users away from dashboard.
   - Requirement now demands credential login access post-registration; flow must be adjusted without weakening security.

4. **Layout-level divergence**
   - Help widget, policy links, and social links are not consistently implemented via shared layouts.
   - Must move to shared include approach to avoid future drift.

---

## Acceptance Criteria (Reviewer Sign-off)

### Public UX + Content
- [ ] No unintended "Kisumu-only" wording remains on public/member surfaces unless explicitly approved as office address.
- [ ] Navigation label shows **Services**.
- [ ] Services include **Cash Benefit** item.
- [ ] Home CTA reads **Talk to Us**.
- [ ] Gallery page is accessible from navigation/footer and renders approved media.
- [ ] Favicon uses approved non-transparent logo and appears correctly in browser tabs.
- [ ] Privacy Policy, T&Cs, and Policy Booklet links exist in all appropriate shared layout areas.
- [ ] Social links are active and point to approved official accounts.

### Pricing/Packages
- [ ] Shared pricing card component is used wherever plan cards appear.
- [ ] "View details" opens a minimal details modal/window per plan.
- [ ] Compare-plans section appears below pricing cards.
- [ ] Package labels/prices/features match approved poster matrix.

### Registration + Identity
- [ ] Public registration is minimal (reduced fields) and retains existing validation/payment logic.
- [ ] Admin/agent registration remains logically consistent with public registration rules.
- [ ] A single canonical member number format is applied by all registration paths.
- [ ] Newly registered members can login with registered credentials immediately (no post-registration conditional blocker).

### Member Portal
- [ ] Upgrade flow no longer fails due to package/ID mismatch and completes expected request lifecycle.
- [ ] Contributions page clearly shows stipulated monthly contribution amount.
- [ ] "Need help?" floating chat affordance appears on all member views.
- [ ] Logout for member/agent/admin redirects to public home with success message.

---

## Non-Functional Requirements
- Preserve current MVC structure and coding patterns.
- Preserve security controls: CSRF validation, input sanitization, auth checks.
- Avoid regressions in payment initiation/verification flows.
- Keep design system consistent; no ad hoc component styles outside existing language.

---

## Dependencies / Inputs Required Before Implementation
1. Approved image replacements and gallery media set.
2. Official package poster matrix (names, prices, feature bullets, order).
3. Official member number format from Excel sheets (exact pattern + sequence rules).
4. Approved links/URLs for:
   - Policy booklet file
   - Privacy policy page
   - Terms & Conditions page
   - Social platforms
5. Confirmation on whether "regional/kenya" copy should preserve one explicit HQ address block.

---

## Delivery Plan (Suggested)
1. Shared layout and content harmonization (labels, links, help icon, favicon, social links).
2. Shared pricing card + details modal + compare block adoption.
3. Gallery page + routing + nav/footer entry.
4. Registration flow harmonization and canonical member-number utility.
5. Member portal fixes (upgrade normalization, contribution hint, help icon, logout redirects).
6. Regression pass: registration, login, payments, upgrade, role-based logout.

---

## Test & Validation Checklist
- Public pages: home/about/services/membership/contact/gallery
- Registration: public self-register, admin register, agent register
- Login/logout: member/agent/admin
- Pricing cards consistency across all pages using plan cards
- Upgrade workflow: request -> payment initiation -> status polling -> completion/cancel
- Contribution page display for monthly contribution amount
- Policy/privacy/T&Cs links and social links on desktop/mobile

---

## Out of Scope (unless added via change request)
- New pricing logic/business rules beyond poster alignment.
- New payment providers or major payment flow redesign.
- Rebranding beyond requested copy/image/logo/icon updates.

---

## Proposed Priority
- **Priority:** P1 (High)
- **Type:** Epic / Multi-workstream product + engineering issue
- **Owner:** Full-stack + UI/UX + QA
- **Target:** Single coordinated release with staged QA sign-off
