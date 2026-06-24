---
description: طلب خطة معمارية لميزة جديدة
---
# Command: plan — ساهم (Sahem)

## Description
ارسم خطة لإضافة ميزة جديدة. اشرح الجداول المطلوبة، العلاقات، الـ Services، المسارات، صفحات Filament، ومواقع الصفحات.

## Usage
```
@opencode plan Add crypto donation support
@opencode plan Fix webhook idempotency bug
@opencode plan --detailed   # Full plan with file list and pseudo-code
```

## What This Does
1. **Requirements Analysis**
   - Clarifies ambiguous requirements via questions
   - Identifies affected models, controllers, services, views
   - Checks for existing similar patterns in codebase

2. **Impact Assessment**
   - Lists all files that will be created or modified
   - Identifies potential breaking changes
   - Estimates migration needs (new tables, columns)
   - Checks for translation requirements

3. **Implementation Plan Output**
```
## Plan: [Feature Name]

### Summary
Brief description of what will be built

### Database (Migrations)
- Table: xxx
- Columns: ...
- Indexes: ...

### Models
- New: ModelName.php
- Modified: ExistingModel.php

### Services
- New: ServiceName.php
- Methods: ...

### Routes
- GET /{locale}/xxx
- POST /{locale}/xxx (throttled)

### Filament
- Resource: XxxResource
- Widgets: ...

### Security
- Policy: XxxPolicy
- Permissions: view_xxx, create_xxx, etc.

### Tests
- Feature: xxx flow
- Unit: Service methods
- Edge: ...

### Steps
1. Create migration
2. Create Model
3. Create Service
4. Create Controller + Routes
5. Create Filament Resource
6. Add translations (5 languages)
7. Write tests
8. Run `php artisan test`
```
