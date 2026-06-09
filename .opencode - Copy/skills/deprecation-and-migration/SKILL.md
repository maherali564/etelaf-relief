---
name: deprecation-and-migration
description: Manages deprecation and migration. Use when removing old systems, APIs, or features.
---

# Deprecation and Migration

## Overview
Code is a liability, not an asset. Every line of code has ongoing maintenance cost. Deprecation is the discipline of removing code that no longer earns its keep.

## Core Principles
- **Code Is a Liability**: Every line has ongoing cost (tests, docs, security patches, mental overhead).
- **Hyrum's Law Makes Removal Hard**: With enough users, every observable behavior becomes depended on.
- **Deprecation Planning Starts at Design Time**: Design with clean interfaces, feature flags, and minimal surface area.

## The Migration Process

### Step 1: Build the Replacement
Don't deprecate without a working alternative that covers all critical use cases.

### Step 2: Announce and Document
Provide status, replacement, removal date, reason, and migration guide.

### Step 3: Migrate Incrementally
Migrate consumers one at a time. The Churn Rule: if you own the infrastructure, you are responsible for migrating users.

### Step 4: Remove the Old System
Only after all consumers have migrated: verify zero usage, remove code, tests, docs, config.

## Migration Patterns
- **Strangler Pattern**: Run old and new in parallel, route traffic incrementally.
- **Adapter Pattern**: Translate old interface to new implementation.
- **Feature Flag Migration**: Use flags to switch consumers one at a time.

## Zombie Code
Code nobody owns but everybody depends on. Either assign an owner and maintain, or deprecate with concrete migration plan.

## Verification
- [ ] Replacement is production-proven
- [ ] Migration guide exists with concrete steps
- [ ] All active consumers migrated
- [ ] Old code, tests, docs, config fully removed
