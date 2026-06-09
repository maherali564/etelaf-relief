# ADR-003: Multilingual Content Strategy

## Status
Accepted

## Context
The platform must support 5 languages (Arabic, English, Spanish, Indonesian, Turkish) with RTL support for Arabic.

## Decision
Use spatie/laravel-translatable for database content and Laravel's __() helper for UI strings. Locale is passed as a URL prefix for all public routes.

## Consequences
- Content editors can enter translations in admin panel
- SEO-friendly URLs with locale prefix
- RTL styling required for Arabic
