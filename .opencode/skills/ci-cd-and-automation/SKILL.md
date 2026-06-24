---
name: ci-cd-and-automation
description: Sets up CI/CD pipelines and automation workflows. Use when configuring continuous integration, deployment pipelines, or automated testing infrastructure.
---

# CI/CD and Automation

## Overview
Automated pipelines ensure consistent quality, catch regressions early, and enable reliable deployments.

## CI Pipeline Essentials
1. **Lint/Static Analysis** — Run Pint, PHPStan, or ESLint
2. **Unit Tests** — Run fast feedback test suite
3. **Integration Tests** — Test with real database
4. **Security Audit** — composer audit, npm audit
5. **Build/Compile** — Verify production build works

## Deployment Pipeline
1. **Staging Deploy** — Auto-deploy main branch to staging
2. **Integration Tests** — Run full suite against staging
3. **Smoke Tests** — Verify critical user flows
4. **Production Deploy** — Manual approval gate
5. **Post-Deploy Monitoring** — Check errors, performance

## Key Practices
- Fail fast: stop pipeline on first failure
- Cache dependencies between runs
- Parallelize independent jobs
- Use environment-specific secrets
- Version pipeline config in repository

## Verification
- [ ] CI pipeline runs on every PR
- [ ] All tests pass before merge
- [ ] Security audit passes
- [ ] Deployment is automated (or has one-click script)
- [ ] Rollback plan exists
