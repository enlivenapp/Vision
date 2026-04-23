[![Version](http://poser.pugx.org/enlivenapp/vision/version)](https://packagist.org/packages/enlivenapp/vision)
[![License](http://poser.pugx.org/enlivenapp/vision/license)](https://packagist.org/packages/enlivenapp/vision)
[![Suggesters](http://poser.pugx.org/enlivenapp/vision/suggesters)](https://packagist.org/packages/enlivenapp/vision)
[![PHP Version Require](http://poser.pugx.org/enlivenapp/vision/require/php)](https://packagist.org/packages/enlivenapp/vision)
[![Monthly Downloads](https://poser.pugx.org/enlivenapp/vision/d/monthly)](https://packagist.org/packages/enlivenapp/vision)

# Vision

Lightweight, framework-agnostic PHP template engine with auto-escaping, template inheritance, includes, filters, and custom tags.

## Requirements

- PHP 8.1+

No framework dependencies. Works with any PHP project.

## Installation

```bash
composer require enlivenapp/vision
```

## Quick Start

Create the engine once, optionally register any custom tags or filters your app needs, then render `.tpl` files:

```php
use Enlivenapp\Vision\Engine;

$vision = new Engine();

// Optional: register custom tags (callable inside {% %})
$vision->tags()->register('base_url', fn(string $path = '') => '/myapp/' . ltrim($path, '/'));
$vision->tags()->register('current_year', fn() => date('Y'));

// Optional: register custom filters (used with | in templates)
$vision->filters()->register('slug', fn($val) => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $val)));

echo $vision->render('/path/to/views/page.tpl', [
    'title' => 'My Page',
    'items' => ['one', 'two', 'three'],
    'user'  => ['name' => 'Admin', 'email' => 'admin@example.com'],
]);
```

That's the full lifecycle. The rest of this document covers template syntax and the engine API in detail.

## Template Syntax

Vision templates are plain `.tpl` files. Four delimited constructs are recognized — everything outside them is emitted verbatim.

| Delimiter | Purpose |
|---|---|
| `{{ ... }}` | Output an expression, auto-escaped |
| `{! ... !}` | Output an expression, raw (no escaping) |
| `{% ... %}` | Control flow, blocks, includes, extends, and custom tags |
| `{# ... #}` | Comments (stripped during lexing — never reach output) |

### Output

`{{ ... }}` is the default output form. Results are passed through `htmlspecialchars()` with `ENT_QUOTES | ENT_SUBSTITUTE` and UTF-8, so output is safe by default:

```html
{{ variable }}           Auto-escaped
{{ user.name }}          Dot-notation into arrays or objects
{{ user.profile.bio }}   Nested access at any depth
```

If any step in a dot-notation chain is missing, the whole expression resolves to `null` silently.

Use `{! ... !}` when you *intentionally* want unescaped output (e.g. pre-rendered HTML from a trusted source):

```html
{! article_html !}
```

### Variables, literals, and expressions

Inside output and `{% %}` tags, Vision accepts:

- **Variables** — `user`, `user.name`, `items.0.title`
- **String literals** — `'hello'` or `"world"` (either quote style; `\` escapes the next character)
- **Number literals** — `42`, `3.14`
- **Keyword literals** — `true`, `false`, `null`

### Conditionals

Branch on a truthy/falsy expression. `elseif` and `else` are optional.

```html
{% if show_banner %}
    <div>Banner</div>
{% endif %}

{% if user %}
    <p>Hello {{ user.name }}</p>
{% else %}
    <p>Please log in</p>
{% endif %}

{% if role == 'admin' %}
    <p>Admin panel</p>
{% elseif role == 'editor' %}
    <p>Editor tools</p>
{% else %}
    <p>Read only</p>
{% endif %}
```

Supported operators: `==`, `!=`, `>`, `<`, `>=`, `<=`, `and`, `or`, `not`.

Vision uses its own truthiness rules — see [Notes & Gotchas](#notes--gotchas).

### Loops

Iterate over any `iterable` value — arrays, `Traversable` objects, and generators all work:

```html
{% for item in items %}
    <li>{{ item }}</li>
{% endfor %}

{% for post in posts %}
    <h2>{{ post.title }}</h2>
    <p>{{ post.excerpt }}</p>
{% endfor %}
```

If the value is not iterable, the loop body is skipped and emits nothing.

### Filters

Filters transform a value using the `|` pipe syntax, and they chain left-to-right:

```html
{{ title | upper }}
{{ name | default('Anonymous') }}
{{ date | date('F j, Y') }}
{{ amount | number_format(2) }}
{{ description | excerpt(100) }}
{{ html_content | strip_tags }}
{{ bio | strip_tags | lower }}
```

#### Built-in filters

| Filter | Arguments | Behavior |
|---|---|---|
| `default(fallback)` | `fallback` default `''` | Returns `fallback` when the value is `null`, `''`, or `false`. `0`, `'0'`, and `[]` are **not** treated as empty. |
| `upper` | — | `mb_strtoupper` (UTF-8 safe). |
| `lower` | — | `mb_strtolower` (UTF-8 safe). |
| `date(format)` | `format` default `'Y-m-d'` | Formats a numeric timestamp or any `strtotime`-parseable string. Returns the input unchanged if parsing fails. |
| `number_format(decimals)` | `decimals` default `0` | PHP's `number_format` with default `,` thousands and `.` decimal separators. |
| `excerpt(length)` | `length` default `150` | Strips tags, then truncates to `length` characters, trims trailing punctuation/whitespace, and appends `…`. Strings already shorter than `length` are returned with tags stripped. |
| `strip_tags` | — | PHP's `strip_tags`. |
| `nl2br` | — | PHP's `nl2br`. Use with `{! !}` so the inserted `<br>` is not escaped. |
| `md5` | — | `md5()` of the string form of the value. |
| `count` | — | `count()` of any array or `Countable`; `0` otherwise. |
| `raw` | — | Marker that tells `{{ }}` to skip escaping. Only meaningful as the final filter in the chain. |

Unknown filter names return the input unchanged — no error.

#### Custom filters

```php
$vision->filters()->register('reverse', fn($val) => strrev((string) $val));
$vision->filters()->register('truncate', fn($val, $len = 100) => mb_substr($val, 0, $len) . '...');
```

Custom filters receive the piped value as their first argument; any arguments in parentheses follow.

### Custom tags

Custom tags are plain function calls inside a `{% %}` block — useful for URL helpers, site-wide values, translation lookups, and similar:

```html
<link href="{% base_url 'css/style.css' %}" rel="stylesheet">
<footer>&copy; {% current_year %}</footer>
```

Arguments are whitespace-separated and may be any expression (literal, variable, filter chain). No tags are registered by default. **Unregistered tags produce no output** — no warning, no exception, no placeholder.

### Template inheritance

Define a parent layout with one or more `{% block %}...{% endblock %}` regions:

**layout.tpl**
```html
<!DOCTYPE html>
<html>
<head><title>{{ title }}</title></head>
<body>
    <header>Site Header</header>
    {% block content %}Default content{% endblock %}
    <footer>Site Footer</footer>
</body>
</html>
```

A child template declares `{% extends %}` and overrides whichever blocks it cares about:

**page.tpl**
```html
{% extends 'layout' %}

{% block content %}
<h1>{{ title }}</h1>
<p>{{ body }}</p>
{% endblock %}
```

Blocks the child does not override fall through to the parent's default content.

### Includes

Pull one template into another:

```html
{% include 'partials/sidebar' %}
{% include 'partials/post-card' with {post: post, featured: true} %}
```

Included templates inherit the parent's full variable scope. The optional `with { key: value, ... }` clause adds or overrides variables for the included template only — the parent's scope is not mutated.

Missing includes silently produce no output. When using the `with` clause, the template name **must** be a quoted string (see [Notes & Gotchas](#notes--gotchas)).

## The Engine API

### `new Engine()`

Takes no arguments. Create one instance per application and reuse it.

### `Engine::render(string $templatePath, array $data, ?string $basePath = null): string`

| Parameter | Purpose |
|---|---|
| `$templatePath` | Absolute path to the `.tpl` file to render. |
| `$data` | Variable context exposed to the template. |
| `$basePath` | Optional base directory for resolving `{% include %}` and `{% extends %}` names. Defaults to `dirname($templatePath) . '/'`. |

Returns the rendered string, or `''` if `$templatePath` does not exist.

Set `$basePath` explicitly when your layouts or partials live in a different directory from the template being rendered — for example, a shared `views/layouts/` tree referenced from module-local templates:

```php
$vision->render(
    '/app/modules/blog/views/post.tpl',
    $data,
    '/app/shared/views/'   // includes/extends resolve from here
);
```

### `Engine::filters(): FilterRegistry`

Returns the filter registry. Call `->register(string $name, callable $callback)` to add custom filters.

### `Engine::tags(): TagRegistry`

Returns the tag registry. Call `->register(string $name, callable $callback)` to add custom tags.

## File Extension

Templates use the `.tpl` extension by convention. Include and extends names have `.tpl` appended automatically if not present, so both `{% include 'partials/sidebar' %}` and `{% include 'partials/sidebar.tpl' %}` resolve to the same file.

## Security

- **Auto-escaping** — all `{{ }}` output is escaped with `htmlspecialchars()` using `ENT_QUOTES | ENT_SUBSTITUTE` and UTF-8 encoding.
- **Raw output is deliberate** — unescaped output requires the distinct `{! !}` syntax, so it's never accidental.
- **No PHP execution** — templates cannot evaluate arbitrary PHP. The expression grammar only supports variable lookup, literals, comparisons, boolean logic, and registered filters/tags.
- **Path-traversal protection** — include and extends template names containing `..` or null bytes resolve to empty and are never read from disk.
- **Comments stripped at lex time** — `{# ... #}` content is discarded before parsing and never reaches output.

## Notes & Gotchas

These are the specific behaviors most likely to surprise you. Nothing here is a bug — each is intentional — but they're worth knowing up front.

- **Truthiness is custom, not PHP-standard.** Inside `{% if %}`, the values `null`, `false`, `''`, `0`, `'0'`, and `[]` are falsy; everything else is truthy. This matches most template engines.
- **`default` does not treat `0` as empty.** `{{ count | default('none') }}` renders `0` when `count` is zero, not `'none'`. Only `null`, `''`, and `false` trigger the fallback. If you need a broader "emptiness" check, write a custom filter.
- **Missing things fail silently.** Missing variables resolve to `null`. Missing includes, missing extends parents, missing top-level templates, unregistered tags, and unknown filters all produce empty output with no warning. This keeps partial renders alive but means typos can go unnoticed — check your output, or wrap `render()` with your own logging if you need strictness.
- **`{% include ... with { ... } %}` requires a quoted template name.** `{% include 'card' with {x: 1} %}` works; `{% include card with {x: 1} %}` does not — the `with` clause is dropped and the name is taken literally. Plain includes without a `with` clause accept quoted or unquoted names.
- **Only the first `{% extends %}` is honored.** A template cannot extend multiple parents; any extra `extends` statements are ignored.
- **The `raw` filter only matters as the last filter on `{{ }}`.** `{{ html | raw }}` skips escaping. `{{ html | raw | upper }}` does **not** — the last filter is `upper`, not `raw`. For raw output, prefer the `{! !}` delimiter; it's clearer.
- **`nl2br` output needs `{! !}`.** `{{ text | nl2br }}` will escape the inserted `<br>` tags back into `&lt;br&gt;`. Use `{! text | nl2br !}` for the intended effect.
- **`.tpl` is appended automatically.** You don't need to include the extension in `include` / `extends` names unless you want to — both forms work.
- **`true`, `false`, `null` are literals.** `{% if active == true %}` and `{{ value | default(null) }}` treat these as PHP literals, not variable names.

## Tests

See [TESTS.md](TESTS.md) for the full test suite results covering output, escaping, conditionals, loops, filters, tags, includes, inheritance, literals, and security.

## License

MIT
