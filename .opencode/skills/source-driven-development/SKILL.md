---
name: source-driven-development
description: Grounds every implementation decision in official documentation. Use when building with any framework or library where correctness matters.
---

# Source-Driven Development

## Overview
Every framework-specific code decision must be backed by official documentation. Don't implement from memory — verify, cite, and let the user see your sources.

## When to Use
- User wants code following current best practices
- Building boilerplate patterns that will be copied
- User explicitly asks for documented, verified implementation
- Any time you write framework-specific code from memory

## The Process

### Step 1: Detect Stack and Versions
Read the project's dependency file to identify exact versions: composer.json, package.json, requirements.txt.

### Step 2: Fetch Official Documentation
Fetch the specific page for the feature you're implementing.
**Source hierarchy:** Official docs > Official blog > Web standards > Browser/runtime compatibility.
**Not authoritative:** Stack Overflow, blog posts, AI-generated summaries, your training data.

### Step 3: Implement Following Documented Patterns
Use API signatures from docs, not from memory. If docs deprecate a pattern, don't use it.

### Step 4: Cite Your Sources
Every framework-specific pattern gets a citation with full URL.

## Common Rationalizations
- "I'm confident about this API" → Confidence is not evidence. Training data contains outdated patterns.
- "Fetching docs wastes tokens" → Hallucinating an API wastes more.
- "This is a simple task, no need to check" → Simple tasks with wrong patterns become templates.

## Red Flags
- Writing framework code without checking docs for that version
- Using "I believe" about an API instead of citing source
- Using deprecated APIs from training data
- Delivering code without source citations for framework decisions

## Verification
- [ ] Framework/library versions identified from dependency file
- [ ] Official docs fetched for framework-specific patterns
- [ ] Code follows current version's documented patterns
- [ ] Non-trivial decisions include source citations with full URLs
- [ ] No deprecated APIs used
- [ ] Unverified patterns explicitly flagged
