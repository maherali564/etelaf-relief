---
name: browser-testing-with-devtools
description: Uses Chrome DevTools for browser-based testing and debugging. Use when testing UI changes, debugging frontend issues, verifying responsive design, or analyzing network/performance.
---

# Browser Testing with DevTools

## Overview
Use Chrome DevTools to verify frontend behavior, debug rendering issues, analyze network requests, and measure performance.

## The DevTools Debugging Workflow
1. REPRODUCE — Navigate to page, trigger bug, screenshot
2. INSPECT — Console errors? DOM structure? Network responses?
3. DIAGNOSE — Compare actual vs expected
4. FIX — Implement the fix in source code
5. VERIFY — Reload, screenshot, confirm console clean

## What to Check
| Tool | When | What to Look For |
|------|------|-----------------|
| Console | Always | Zero errors and warnings |
| Network | API issues | Status codes, payload shape, timing |
| DOM | UI bugs | Element structure, attributes, accessibility |
| Styles | Layout issues | Computed styles vs expected |
| Performance | Slow pages | LCP, CLS, INP, long tasks |
| Screenshots | Visual changes | Before/after comparison |

## Verification
- [ ] Console has zero errors
- [ ] Network requests return expected status codes
- [ ] DOM matches expected structure
- [ ] Layout matches design at all breakpoints
- [ ] Performance metrics within budget
