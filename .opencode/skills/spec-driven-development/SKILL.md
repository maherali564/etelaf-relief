---
name: spec-driven-development
description: Creates specs before coding. Use when starting a new project, feature, or significant change.
---

# Spec-Driven Development

## Overview
Write a structured specification before writing any code. The spec is the shared source of truth between you and the human engineer.

## The Gated Workflow
```
SPECIFY → PLAN → TASKS → IMPLEMENT
```

### Phase 1: Specify
Surface assumptions immediately before writing spec content. Write a spec covering:
1. **Objective** — What are we building and why?
2. **Commands** — Build, test, lint, dev commands
3. **Project Structure** — Where code lives
4. **Code Style** — One real code snippet
5. **Testing Strategy** — Framework, coverage expectations
6. **Boundaries** — Always do / Ask first / Never do

Reframe vague instructions as concrete success criteria.

### Phase 2: Plan
Generate technical implementation plan: components, dependencies, order, risks.

### Phase 3: Tasks
Break plan into discrete tasks. Each: completable in one session, has acceptance criteria, includes verification step.

### Phase 4: Implement
Execute tasks one at a time following incremental-implementation and test-driven-development skills.

## Keeping the Spec Alive
- Update when decisions change
- Update when scope changes
- Commit the spec to version control
- Reference the spec in PRs

## Red Flags
- Starting code without any written requirements
- Implementing features not mentioned in spec
- Making architectural decisions without documenting them
- Skipping the spec because "it's obvious"

## Verification
- [ ] Spec covers all six core areas
- [ ] Human has reviewed and approved the spec
- [ ] Success criteria are specific and testable
- [ ] Boundaries defined (Always/Ask First/Never)
