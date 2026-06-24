---
name: context-engineering
description: Manages context windows efficiently. Use when working on large codebases or complex tasks that exceed context limits.
---

# Context Engineering

## Overview
Efficient context management for AI agents working on large codebases. Load only what's needed, when it's needed.

## Principles
1. **Load Lazily** — Read files only when needed, not preemptively
2. **Reference by Path** — Use file paths as anchors; re-read when necessary
3. **Summarize Dense Content** — Distill long files into essential facts
4. **Prefer Diff Over Full File** — Show only changed lines when possible
5. **Use Memory Files** — Keep project state in .opencode/memory/

## Patterns
- Read interface/contract first, then implementation
- Read tests before production code when fixing bugs
- Read config files once, cache key details in memory
- Group related files in parallel reads

## Verification
- [ ] Only relevant files loaded
- [ ] Key decisions documented in memory files
- [ ] Context not exceeded during task
