---
name: documentation-and-adrs
description: Creates and maintains documentation and Architecture Decision Records. Use when documenting architectural decisions, writing READMEs, or creating technical documentation.
---

# Documentation and ADRs

## Overview
Documentation captures knowledge that code cannot express. Architecture Decision Records (ADRs) document why decisions were made.

## When to Document
- Architectural decisions that affect multiple components
- Trade-offs that were considered and rejected
- Configuration or setup steps
- APIs and interfaces
- Complex business logic

## ADR Format
```markdown
# ADR-{number}: {Title}

## Status
Proposed | Accepted | Deprecated | Superseded

## Context
What is the problem? What constraints exist?

## Decision
What was decided and why?

## Consequences
What trade-offs exist? What must change?
```

## Documentation Principles
- Keep docs close to code (same repo, same branch)
- Update docs when code changes
- Use examples over abstract descriptions
- Document WHY, not just WHAT

## Verification
- [ ] ADR exists for every architectural decision
- [ ] Documentation is in version control
- [ ] README is up to date
- [ ] API docs reflect current implementation
