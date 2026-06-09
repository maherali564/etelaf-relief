---
name: code-simplification
description: Simplifies code for clarity. Use when refactoring code for clarity without changing behavior.
---

# Code Simplification

## Overview
Simplify code by reducing complexity while preserving exact behavior. The goal is not fewer lines — it's code that is easier to read, understand, modify, and debug.

## The Five Principles

### 1. Preserve Behavior Exactly
Don't change what the code does — only how it expresses it. All existing tests must still pass.

### 2. Follow Project Conventions
Simplification means making code more consistent with the codebase. Match the project's style.

### 3. Prefer Clarity Over Cleverness
Explicit code is better than compact code when the compact version requires a mental pause to parse.

### 4. Maintain Balance
Don't over-simplify: inlining too aggressively, combining unrelated logic, removing necessary abstractions.

### 5. Scope to What Changed
Default to simplifying recently modified code. Avoid drive-by refactors of unrelated code.

## Simplification Process

### Step 1: Understand Before Touching (Chesterton's Fence)
Before changing anything, understand why it exists. If you can't answer what calls it, what it calls, and what the edge cases are — you're not ready to simplify.

### Step 2: Identify Opportunities
- Deep nesting (3+ levels) → Extract into guard clauses
- Long functions (50+ lines) → Split into focused functions
- Nested ternaries → Replace with if/else or switch
- Generic names (data, result, temp) → Rename to describe content
- Duplicated logic → Extract to shared function
- Dead code → Remove after confirming

### Step 3: Apply Incrementally
One simplification at a time. Run tests after each change.

## Red Flags
- Simplification requiring test modifications (you changed behavior)
- "Simplified" code harder to follow than original
- Removing error handling because "it makes code cleaner"
- Batching many simplifications into one large commit

## Verification
- [ ] All existing tests pass without modification
- [ ] Build succeeds with no new warnings
- [ ] Each simplification is a reviewable, incremental change
- [ ] No error handling was removed or weakened
- [ ] Simplified code follows project conventions
