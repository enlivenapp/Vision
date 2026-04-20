# Vision Test Results

All tests pass against Vision v0.1.0.

## 1. Output & Escaping

| Test | Expected | Result |
|------|----------|--------|
| Auto-escaped string | `&lt;script&gt;alert(1)&lt;/script&gt;` | PASS |
| Raw output `{! !}` | `<strong>bold</strong>` rendered as HTML | PASS |
| Integer output | `42` | PASS |
| Float output | `3.14` | PASS |
| Empty string | (empty) | PASS |
| Null value | (empty) | PASS |
| Undefined variable | (empty) | PASS |

## 2. Dot Notation Access

| Test | Expected | Result |
|------|----------|--------|
| `user.name` | `Alice` | PASS |
| `user.email` | `alice@example.com` | PASS |
| `user.address.city` (3-deep) | `Portland` | PASS |
| `user.nonexistent` | (empty) | PASS |

## 3. Conditionals

| Test | Result |
|------|--------|
| Simple `if` (truthy) | PASS |
| Simple `if` (falsy, should not render) | PASS |
| `if/else` | PASS |
| `if/elseif/else` | PASS |
| `==` comparison | PASS |
| `!=` comparison | PASS |
| `>` comparison | PASS |
| `<` comparison | PASS |
| `>=` comparison | PASS |
| `<=` comparison | PASS |
| `and` (true AND true) | PASS |
| `and` (false AND true, should not render) | PASS |
| `or` (false OR true) | PASS |
| `not` (NOT false) | PASS |
| `not` (NOT true, should not render) | PASS |
| Empty string is falsy | PASS |
| Zero is falsy | PASS |
| Null is falsy | PASS |
| Empty array is falsy | PASS |

## 4. Loops

| Test | Result |
|------|--------|
| Simple array iteration | PASS — rendered red, green, blue |
| Array of objects/arrays | PASS — rendered name/age table |
| Empty collection (no output) | PASS |
| Non-iterable (no output) | PASS |

## 5. Built-in Filters

| Filter | Input | Expected | Result |
|--------|-------|----------|--------|
| `default` (missing var) | `undefined_var` | `fallback` | PASS |
| `default` (present var) | `hello` | `hello` | PASS |
| `upper` | `hello` | `HELLO` | PASS |
| `lower` | `SHOUTING` | `shouting` | PASS |
| `strip_tags` | `<b>bold</b>` | `bold` | PASS |
| `nl2br` | `line1\nline2` | `line1<br />\nline2` | PASS |
| `date` | `2026-04-18` | `April 18, 2026` | PASS |
| `number_format(2)` | `1234567.891` | `1,234,567.89` | PASS |
| `excerpt(30)` | (long text) | truncated + `...` | PASS |
| `count` | `['red','green','blue']` | `3` | PASS |
| `raw` (via filter) | `<em>italic</em>` | rendered as HTML | PASS |
| Chained: `strip_tags \| lower` | `<b>HELLO</b>` | `hello` | PASS |
| Unknown filter (passthrough) | `hello` | `hello` | PASS |

## 6. Custom Tags

| Tag | Expected | Result |
|-----|----------|--------|
| `{% greet %}` (no args) | `Hello, World!` | PASS |
| `{% greet 'Vision' %}` (with arg) | `Hello, Vision!` | PASS |
| `{% unregistered_tag %}` | (empty) | PASS |

## 7. Custom Filters

| Filter | Input | Expected | Result |
|--------|-------|----------|--------|
| `reverse` | `hello` | `olleh` | PASS |
| `repeat(3)` | `hello` | `hellohellohello` | PASS |

## 8. Includes

| Test | Result |
|------|--------|
| Simple include (inherits parent scope) | PASS — `partial_message` rendered |
| Include with `with` data | PASS — `item_name` and parent `title` both rendered |
| Missing include (silent) | PASS — no output, no error |

## 9. Template Inheritance

| Test | Result |
|------|--------|
| `{% extends %}` loads parent layout | PASS |
| `{% block content %}` overrides parent block | PASS |
| `{% block head_extra %}` injects into parent head | PASS |
| `{% block footer %}` overrides footer | PASS |

## 10. Literals in Expressions

| Literal | Expected | Result |
|---------|----------|--------|
| String `'literal string'` | `literal string` | PASS |
| Integer `99` | `99` | PASS |
| Float `2.5` | `2.5` | PASS |
| Boolean `true` | `1` | PASS |
| Boolean `false` | (empty) | PASS |
| `null` | (empty) | PASS |

## 11. Security

| Test | Expected | Result |
|------|----------|--------|
| XSS in HTML attribute | Quotes escaped to `&quot;` | PASS |
| Double-escaping check | `&amp;` not `&amp;amp;` | PASS |
| Path traversal in include | Silent fail, no file access | PASS |
| Comments `{# #}` stripped | Not in output | PASS |

## Summary

**21 behavioral assertions: 21 PASS, 0 FAIL**

All features verified: output escaping, raw output, dot notation, conditionals (if/elseif/else, comparisons, boolean operators, truthiness), loops (arrays, objects, empty, non-iterable), built-in filters, custom filters, custom tags, includes (simple, with data, missing), template inheritance (extends, blocks), literals, and security (XSS, double-escaping, path traversal, comment stripping).
