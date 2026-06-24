---
name: incremental-implementation
description: Implements features in small verifiable steps. Use when building any non-trivial feature to reduce risk and maintain momentum.
---

# Incremental Implementation

## Overview
Build features incrementally — each step leaves the system in a working state. Small steps limit blast radius, make debugging easier, and provide early feedback.

## The Rule
Each increment must leave the system functional. No half-implemented features, no commented-out code, no dead ends.

## Implementation Flow
1. **Scaffold** — Create files, classes, routes (all empty)
2. **Data Layer** — Database migration, model, factory
3. **Business Logic** — Service with tests
4. **Controller** — Wire up route→controller→service
5. **View/UI** — Blade template or API response
6. **Integration** — End-to-end flow works
7. **Polish** — Error handling, edge cases, performance

## Verify After Each Step
- `php artisan test` — Tests pass
- `php artisan route:list` — Routes registered
- Manual check — Feature works in browser

## When to Split
Each increment should be completable in one session. If it takes longer, split further.

## Red Flags
- System in broken state between increments
- Skipping tests to "go faster"
- More than 5 files changed per increment
- Increment that can't be demo'd independently

## Verification
- [ ] Each increment leaves system functional
- [ ] Tests pass after each step
- [ ] Increment is demo-able independently
- [ ] No commented-out or dead code
