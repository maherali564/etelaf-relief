---
name: test-driven-development
description: Drives development with tests. Use when implementing any logic, fixing any bug, or changing any behavior.
---

# Test-Driven Development

## Overview
Write a failing test before writing the code that makes it pass. For bug fixes, reproduce the bug with a test before attempting a fix.

## The TDD Cycle
```
RED (failing test) → GREEN (minimal code to pass) → REFACTOR (clean up)
```

### Step 1: RED — Write a Failing Test
Write the test first. It must fail. A test that passes immediately proves nothing.

### Step 2: GREEN — Make It Pass
Write the minimum code to make the test pass. Don't over-engineer.

### Step 3: REFACTOR — Clean Up
Improve code without changing behavior. Run tests after every refactor step.

## The Prove-It Pattern (Bug Fixes)
For bug reports: write a test that reproduces the bug FIRST. Confirm it fails. Then fix. Then confirm it passes.

## The Test Pyramid
- Unit Tests (~80%): Pure logic, isolated, fast
- Integration Tests (~15%): Component interactions, API boundaries
- E2E Tests (~5%): Full user flows, critical paths

## Writing Good Tests

### Test State, Not Interactions
Assert on outcomes, not method calls. Tests that verify method call sequences break when you refactor.

### DAMP Over DRY in Tests
Tests should read like specifications — each test tells a complete story. Duplication is acceptable for readability.

### Prefer Real Implementations Over Mocks
Real > Fake > Stub > Mock. Use mocks only when real implementation is too slow or non-deterministic.

### One Assertion Per Concept
Each test verifies one behavior. Descriptive test names: it_rejects_empty_titles, it_trims_whitespace.

## Test Anti-Patterns
| Anti-Pattern | Problem |
|---|---|
| Testing implementation details | Tests break on refactoring |
| Flaky tests | Erode trust in suite |
| Testing framework code | Wastes time |
| Mocking everything | Tests pass, production breaks |

## Red Flags
- Writing code without corresponding tests
- Tests passing on first run (may not test what you think)
- Bug fixes without reproduction tests
- Skipping tests to make suite pass

## Verification
- [ ] Every new behavior has a corresponding test
- [ ] All tests pass
- [ ] Bug fixes include reproduction test
- [ ] Test names describe the behavior being verified
- [ ] No tests were skipped
