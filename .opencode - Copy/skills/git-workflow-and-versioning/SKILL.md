---
name: git-workflow-and-versioning
description: Guides Git workflow and versioning strategies. Use when managing branches, commits, releases, or resolving merge conflicts.
---

# Git Workflow and Versioning

## Overview
Consistent Git workflow enables collaboration, traceability, and reliable releases.

## Branch Strategy
- `main` — Production-ready code, protected branch
- `feature/*` — New features (feature/add-stripe-webhook)
- `fix/*` — Bug fixes (fix/webhook-signature)
- `security/*` — Security patches (security/csrf-protection)
- `docs/*` — Documentation updates

## Commit Convention (Conventional Commits)
```
feat(payment): add Stripe webhook idempotency
fix(donate): validate email uniqueness
security(auth): add rate limiting to login
test(donation): add edge case for duplicate webhook
refactor(service): extract donation logic to DonationService
chore(deps): update laravel/framework to 11.5
```

## Commit Best Practices
- Imperative mood: "Add feature" not "Added feature"
- Subject max 72 characters
- Body explains WHY, not WHAT
- Reference issues: `Fixes #123`
- One logical change per commit

## Pull Requests
- Descriptive title and body
- Link to related issues
- Include screenshots for UI changes
- Require at least one reviewer
- All tests must pass
- No merge conflicts

## Verification
- [ ] Branch follows naming convention
- [ ] Commits follow conventional commit format
- [ ] PR has description and references
- [ ] All tests pass
- [ ] No merge conflicts
