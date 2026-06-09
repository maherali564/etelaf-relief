---
name: code-review-and-quality
description: Conducts multi-axis code review. Use before merging any change. Use when reviewing code written by yourself, another agent, or a human.
---

# Code Review and Quality

## Overview
Multi-dimensional code review with quality gates. Every change gets reviewed before merge — no exceptions. Review covers five axes: correctness, readability, architecture, security, and performance.

**Approval standard:** Approve a change when it definitely improves overall code health, even if it isn't perfect.

## The Five-Axis Review

### 1. Correctness
- Does it match the spec or task requirements?
- Are edge cases handled (null, empty, boundary values)?
- Are error paths handled?
- Does it pass all tests?

### 2. Readability & Simplicity
- Are names descriptive and consistent?
- Is the control flow straightforward?
- Could this be done in fewer lines?
- Are abstractions earning their complexity?

### 3. Architecture
- Does it follow existing patterns?
- Does it maintain clean module boundaries?
- No circular dependencies?
- Is the abstraction level appropriate?

### 4. Security
- Is user input validated and sanitized?
- Are secrets kept out of code, logs, and version control?
- Is authentication/authorization checked?
- Are SQL queries parameterized?
- Are outputs encoded to prevent XSS?

### 5. Performance
- Any N+1 query patterns?
- Any unbounded loops or unconstrained data fetching?
- Any missing pagination?
- Any synchronous operations that should be async?

## Change Sizing
- ~100 lines → Good. Reviewable in one sitting.
- ~300 lines → Acceptable if single logical change.
- ~1000 lines → Too large. Split it.

## Review Process
1. Understand the context
2. Review the tests first
3. Review the implementation
4. Categorize findings (Critical / Nit / Optional / FYI)
5. Verify the verification

## Dead Code Hygiene
After refactoring, check for orphaned code. List it and ask before deleting.

## The Review Checklist
- [ ] Change matches spec/task requirements
- [ ] Edge cases handled
- [ ] Error paths handled
- [ ] Tests cover the change adequately
- [ ] Names are clear and consistent
- [ ] No unnecessary complexity
- [ ] Follows existing patterns
- [ ] No unnecessary coupling
- [ ] No secrets in code
- [ ] Input validated at boundaries
- [ ] No N+1 patterns
- [ ] Tests pass, Build succeeds
