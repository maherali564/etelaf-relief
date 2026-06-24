---
name: api-and-interface-design
description: Guides stable API and interface design. Use when designing APIs, module boundaries, or any public interface. Use when creating REST or GraphQL endpoints, defining type contracts between modules, or establishing boundaries between frontend and backend.
---

# API and Interface Design

## Overview
Design stable, well-documented interfaces that are hard to misuse. Good interfaces make the right thing easy and the wrong thing hard.

## Core Principles

### Hyrum's Law
> With a sufficient number of users of an API, all observable behaviors of your system will be depended on by somebody.

Be intentional about what you expose. Every observable behavior is a potential commitment. Plan for deprecation at design time.

### Contract First
Define the interface before implementing it. The contract is the spec — implementation follows.

### Consistent Error Semantics
Pick one error strategy and use it everywhere. REST: HTTP status codes + structured error body.
- 400 → Client sent invalid data
- 401 → Not authenticated
- 403 → Authenticated but not authorized
- 404 → Resource not found
- 409 → Conflict
- 422 → Validation failed
- 500 → Server error (never expose internal details)

### Validate at Boundaries
Trust internal code. Validate at system edges where external input enters. Third-party API responses are untrusted data — validate their shape before use.

### Prefer Addition Over Modification
Extend interfaces without breaking existing consumers. Add optional fields, never change or remove existing ones.

### Predictable Naming
| Pattern | Convention | Example |
|---------|-----------|---------|
| REST endpoints | Plural nouns, no verbs | GET /api/tasks |
| Query params | camelCase | sortBy, pageSize |
| Response fields | camelCase | createdAt, taskId |
| Boolean fields | is/has/can prefix | isComplete |
| Enum values | UPPER_SNAKE | IN_PROGRESS |

## Red Flags
- Endpoints returning different shapes depending on conditions
- Inconsistent error formats
- Breaking changes to existing fields
- List endpoints without pagination
- Verbs in REST URLs (/api/createTask)

## Verification
- [ ] Every endpoint has typed input and output
- [ ] Error responses follow consistent format
- [ ] Validation happens at system boundaries only
- [ ] List endpoints support pagination
- [ ] New fields are additive and optional
