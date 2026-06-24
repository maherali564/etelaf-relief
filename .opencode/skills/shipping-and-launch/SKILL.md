---
name: shipping-and-launch
description: Prepares and ships features to production. Use when a feature is complete and ready for release.
---

# Shipping and Launch

## Overview
Systematic process for taking completed features to production safely.

## Pre-Launch Checklist

### Code Quality
- [ ] All tests pass
- [ ] Code style passes (Pint)
- [ ] No debugging artifacts (dd, dump, ray)
- [ ] composer audit passes
- [ ] No secrets in code

### Performance
- [ ] Database queries optimized (no N+1)
- [ ] Caching configured where appropriate
- [ ] Assets optimized (CSS, JS, images)
- [ ] Config cache, route cache, event cache, view cache refreshed

### Security
- [ ] CSRF protection on all POST forms
- [ ] Throttle middleware on public routes
- [ ] Validation on all inputs
- [ ] Webhooks verify signatures
- [ ] Sensitive data not exposed in logs

### Deployment
- [ ] .env configured with production values
- [ ] Maintenance mode planned
- [ ] Database migrations ready
- [ ] Rollback plan exists
- [ ] Monitoring alerts configured

### Post-Launch
- [ ] Smoke test critical user flows
- [ ] Monitor error rates
- [ ] Monitor response times
- [ ] Verify webhooks processing
- [ ] Check logs for unexpected errors

## Rollback Plan
1. Identify rollback trigger conditions
2. Document rollback steps (git revert, database rollback)
3. Test rollback procedure
4. Set rollback decision timeout

## Verification
- [ ] Pre-launch checklist complete
- [ ] Rollback plan documented
- [ ] Monitoring configured
- [ ] Team notified of launch
