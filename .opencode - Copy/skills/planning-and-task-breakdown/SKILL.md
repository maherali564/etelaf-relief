---
name: planning-and-task-breakdown
description: Breaks features into actionable tasks. Use when starting a new feature, sprint planning, or organizing complex work.
---

# Planning and Task Breakdown

## Overview
Convert feature requirements into ordered, actionable tasks. Each task should be completable independently.

## Breakdown Process

### 1. Identify the Components
List all files that will be created or modified: migrations, models, services, controllers, views, tests.

### 2. Order by Dependency
What must exist first? Data layer → Business logic → API/Controller → UI

### 3. Define Tasks
Each task includes:
- Description of what to do
- Files to create/modify
- Acceptance criteria
- Verification step

### 4. Estimate Effort
Small (< 1hr) → Single task
Medium (1-4hr) → 2-3 tasks
Large (4hr+) → Break down further

## Task Template
```markdown
- [ ] Task: [Action] [Component]
  - Files: [list of files]
  - Acceptance: [what must be true]
  - Verify: [test command or manual check]
```

## Task Sizing
| Size | Definition | Max files |
|------|-----------|-----------|
| Small | One component, no new concepts | 1-2 |
| Medium | Multiple files, single concern | 3-5 |
| Large | Multiple concerns, needs splitting | 6+ |

## Verification
- [ ] All tasks are actionable
- [ ] Dependencies between tasks identified
- [ ] Each task has acceptance criteria
- [ ] No task exceeds 5 files
- [ ] Verification step defined for each task
