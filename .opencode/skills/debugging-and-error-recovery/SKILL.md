---
name: debugging-and-error-recovery
description: Guides systematic root-cause debugging. Use when tests fail, builds break, or behavior doesn't match expectations.
---

# Debugging and Error Recovery

## Overview
Systematic debugging with structured triage. When something breaks, stop adding features, preserve evidence, and follow a structured process.

## The Stop-the-Line Rule
1. STOP adding features or making changes
2. PRESERVE evidence (error output, logs, repro steps)
3. DIAGNOSE using the triage checklist
4. FIX the root cause
5. GUARD against recurrence
6. RESUME only after verification passes

## The Triage Checklist

### Step 1: Reproduce
Make the failure happen reliably. If you can't reproduce it, you can't fix it.

### Step 2: Localize
Narrow down WHERE the failure happens: UI? API? Database? Build tooling? External service? Test itself?

### Step 3: Reduce
Create the minimal failing case. Remove unrelated code until only the bug remains.

### Step 4: Fix the Root Cause
Ask "Why?" until you reach the actual cause. Fix the underlying issue, not the symptom.

### Step 5: Guard Against Recurrence
Write a test that catches this specific failure. It should fail without the fix.

### Step 6: Verify End-to-End
Run the specific test, full suite, build project, manual spot check.

## Error-Specific Patterns
- **Test failure after code change**: Did you change code the test covers? If yes, check if test or code is wrong.
- **Build failure**: Type error, import error, config error, dependency error, environment error.
- **Runtime error**: TypeError (null/undefined), Network error, Render error, Unexpected behavior.

## Safe Fallback Patterns
Use safe defaults with warnings instead of crashing. Use graceful degradation with error boundaries.

## Common Rationalizations
- "I know what the bug is, I'll just fix it" → Reproduce first. You might be wrong 30% of the time.
- "The failing test is probably wrong" → Verify that assumption. Fix the test, don't skip it.
- "It works on my machine" → Environments differ. Check CI, config, dependencies.

## Red Flags
- Skipping a failing test to work on new features
- Guessing at fixes without reproducing the bug
- Fixing symptoms instead of root causes
- No regression test added after a bug fix

## Verification
- [ ] Root cause is identified and documented
- [ ] Fix addresses root cause, not just symptoms
- [ ] Regression test exists that fails without the fix
- [ ] All existing tests pass
