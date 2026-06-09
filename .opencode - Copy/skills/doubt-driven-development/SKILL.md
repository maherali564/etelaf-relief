---
name: doubt-driven-development
description: Subjects every non-trivial decision to a fresh-context adversarial review before it stands.
---

# Doubt-Driven Development

## Overview
A confident answer is not a correct one. Long sessions accumulate context that quietly turns assumptions into "facts." Doubt-driven development is the discipline of fresh-context adversarial review before non-trivial output stands.

## When to Use
A decision is non-trivial when it: introduces/modifies branching logic, crosses module boundaries, asserts properties the type system can't verify, has irreversible blast radius.

## The Process

### Step 1: CLAIM — Surface what stands
Name the decision in 2-3 lines. If you can't write the claim that compactly, you have a vibe, not a decision.

### Step 2: EXTRACT — Smallest reviewable unit
The artifact + contract, not the journey. Strip your reasoning.

### Step 3: DOUBT — Invoke the fresh-context reviewer
Adversarial review prompt: "Find what is wrong. Assume the author is overconfident."
Do NOT pass the CLAIM to the reviewer — it biases toward agreement.

### Step 4: RECONCILE — Fold findings back
Classify findings: contract misread, valid+actionable, valid trade-off, or noise.

### Step 5: STOP — Bounded loop
Stop when: next iteration returns trivial findings, 3 cycles completed, or user says "ship it."

## Common Rationalizations
- "I'm confident, skip the doubt step" → Moments of certainty are when blind spots hide.
- "Spawning a reviewer is expensive" → Debugging a wrong commit in production is more expensive.
- "The reviewer will just nitpick" → Constrain the prompt to "issues that would make this fail."

## Red Flags
- Treating reviewer output as authoritative without re-reading the artifact
- Looping >3 cycles without escalating
- Prompting with "is this good?" instead of "find issues"
- Doubt theater: 2+ substantive findings, zero classified as actionable
- Passing the CLAIM to the reviewer
