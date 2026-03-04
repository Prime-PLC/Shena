# Public + Member Harmonization — Staged Delivery Tracker

Use this file as a monitoring and commit checklist for the epic rollout.

## Stage 1 — Global Content & Branding
- [x] Header label updated to `Services`
- [x] Homepage CTA updated to `Talk to Us`
- [x] Kisumu-only wording harmonized to Kenya-wide wording (except explicit HQ references)
- [x] Favicon wired to approved logo asset
- [x] Footer social links activated
- [x] Global policy links added (Privacy, T&Cs, Policy Booklet)

Suggested commit message:
`feat(ui): harmonize global branding copy, links, and public support footer`

## Stage 2 — Shared Pricing UI
- [x] Reusable pricing card component created
- [x] Plan details modal behavior added per plan
- [x] Compare-plans section added below pricing cards
- [x] Membership page migrated to shared pricing components

Suggested commit message:
`feat(pricing): introduce shared pricing cards, plan detail modals, and compare table`

## Stage 3 — Gallery & Media
- [x] Gallery route added
- [x] Gallery page added with media from `public/images`
- [x] Gallery linked from navigation/footer
- [x] Public imagery refreshed to local project assets where applied

Suggested commit message:
`feat(public): add gallery page and wire local image assets`

## Stage 4 — Registration & Identity Lifecycle
- [x] Canonical member number formatter added (`SHA-YYYY-####`)
- [x] Public/admin/agent registration flows aligned to canonical member number
- [x] Package tier normalization utility added
- [x] Login status gate removed for member credential access post-registration

Suggested commit message:
`fix(auth): unify member numbering and remove post-registration member login blocker`

## Stage 5 — Member Portal Reliability
- [x] Upgrade flow package normalization hardened
- [x] Contributions page shows explicit monthly contribution amount
- [x] Global member floating `Need help?` widget added across member views
- [x] Logout redirect standardized to public home with success flash

Suggested commit message:
`fix(member): normalize upgrade tiers, add help affordance, and standardize logout redirect`

## Stage 6 — Policy & Support Pages
- [x] Privacy Policy page added
- [x] Terms & Conditions page added
- [x] Policy Booklet page added

Suggested commit message:
`feat(public): add policy, terms, and booklet pages with shared navigation links`

## Final Verification Checklist
- [ ] Public pages: `/`, `/about`, `/services`, `/membership`, `/contact`, `/gallery`
- [ ] Registration: public, admin, agent member creation
- [ ] Login/logout: member, agent, admin
- [ ] Upgrade request flow with non-normalized legacy package records
- [ ] Contributions page monthly amount visibility
- [ ] Footer/header policy + social links on desktop/mobile
