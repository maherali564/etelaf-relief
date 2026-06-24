---
name: using-agent-skills
description: Guides how to effectively use and combine available skills. Use when deciding which skills to apply to a given task.
---

# Using Agent Skills

## Overview
This project has specialized skills for different aspects of software engineering. Use the right skill (or combination of skills) for each task.

## How to Choose a Skill

### By Task Type
| Task Type | Primary Skill | Secondary Skill |
|-----------|--------------|-----------------|
| New feature | spec-driven-development | plan, incremental-implementation |
| Bug fix | debugging-and-error-recovery | test-driven-development |
| Code review | code-review-and-quality | security-and-hardening |
| Performance issue | performance-optimization | eloquent-optimization |
| Security concern | security-and-hardening | doubt-driven-development |
| Refactoring | code-simplification | deprecation-and-migration |
| API design | api-and-interface-design | spec-driven-development |
| Deployment | shipping-and-launch | ci-cd-and-automation |

### By Development Phase
1. **Planning** — spec-driven-development, idea-refine, planning-and-task-breakdown
2. **Building** — test-driven-development, incremental-implementation, source-driven-development
3. **Reviewing** — code-review-and-quality, doubt-driven-development
4. **Shipping** — shipping-and-launch, ci-cd-and-automation
5. **Maintaining** — debugging-and-error-recovery, deprecation-and-migration

## Skill Combinations
- TDD + Source-Driven: Write test based on docs, implement to pass
- Doubt-Driven + Security: Adversarial review of security-critical code
- Spec-Driven + Incremental: Plan the spec, implement incrementally

## Verification
- [ ] Appropriate skill selected for task
- [ ] Skills combined effectively when needed
- [ ] Skill instructions followed completely
