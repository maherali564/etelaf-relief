---
name: frontend-ui-engineering
description: Guides frontend UI implementation. Use when building or modifying Blade templates, CSS, or JavaScript frontend features.
---

# Frontend UI Engineering

## Overview
Build accessible, responsive, and performant user interfaces using Blade, CSS, and JavaScript.

## Principles
1. **Mobile First** — Design for small screens first, enhance for larger
2. **Accessible** — Semantic HTML, ARIA labels, keyboard navigation
3. **RTL Support** — Arabic requires proper RTL styling
4. **Progressively Enhanced** — Core functionality works without JS

## Blade Templates
- Use `{{ }}` for safe output, `{!! !!}` only for trusted HTML
- Use `@section` and `@extends` for layout inheritance
- Keep logic in controllers/services, not views
- Use partials for reusable components

## CSS/Styling
- Use CSS custom properties for theming
- Support RTL with `dir="rtl"` on html element
- Use responsive breakpoints consistently
- Avoid `!important` — use specificity instead

## JavaScript
- Use Swiper.js for sliders (already in project)
- Use Chart.js for admin charts (already in project)
- Use Font Awesome for icons (already in project)
- Load scripts at bottom of body
- Use event delegation for dynamic content

## Accessibility Checklist
- [ ] All images have alt text
- [ ] Forms have labels
- [ ] Color contrast meets WCAG AA
- [ ] Keyboard navigation works
- [ ] Screen reader tested
