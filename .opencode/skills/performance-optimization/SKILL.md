---
name: performance-optimization
description: Optimizes application performance. Use when performance requirements exist or load times need improvement.
---

# Performance Optimization

## Overview
Measure before optimizing. Performance work without measurement is guessing. Profile first, identify the actual bottleneck, fix it, measure again.

## Core Web Vitals Targets
| Metric | Good | Poor |
|--------|------|------|
| LCP | ≤ 2.5s | > 4.0s |
| INP | ≤ 200ms | > 500ms |
| CLS | ≤ 0.1 | > 0.25 |

## The Optimization Workflow
1. MEASURE → Establish baseline with real data
2. IDENTIFY → Find the actual bottleneck
3. FIX → Address the specific bottleneck
4. VERIFY → Measure again, confirm improvement
5. GUARD → Add monitoring or tests

## Common Bottlenecks

### Backend
| Symptom | Likely Cause |
|---------|-------------|
| Slow API responses | N+1 queries, missing indexes |
| Memory growth | Leaked references, unbounded caches |
| High latency | Missing caching, redundant computation |

### Frontend
| Symptom | Likely Cause |
|---------|-------------|
| Slow LCP | Large images, render-blocking resources |
| High CLS | Images without dimensions, font shifts |
| Poor INP | Heavy JavaScript, large DOM updates |

## Fix Anti-Patterns

### N+1 Queries
```php
// Bad
$tasks = Task::all();
foreach ($tasks as $task) { echo $task->user->name; }

// Good
$tasks = Task::with('user')->get();
```

### Unbounded Data Fetching
```php
// Bad: Fetching all records
$all = Model::all();

// Good: Paginated
$items = Model::orderBy('created_at', 'desc')->paginate(20);
```

### Missing Caching (Backend)
```php
$data = Cache::remember('key', 3600, fn() => Model::sum('amount'));
```

## Performance Budget
- JavaScript bundle: < 200KB gzipped
- API response time: < 200ms (p95)
- Time to Interactive: < 3.5s on 4G
- Lighthouse Performance: ≥ 90

## Red Flags
- Optimization without profiling data
- N+1 query patterns
- List endpoints without pagination
- Images without dimensions or lazy loading
- No performance monitoring in production

## Verification
- [ ] Before and after measurements exist
- [ ] Specific bottleneck identified and addressed
- [ ] Core Web Vitals within "Good" thresholds
- [ ] No N+1 queries in new data fetching code
- [ ] Existing tests still pass
