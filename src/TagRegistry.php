<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

class TagRegistry
{
    /** @var array<string, callable> */
    private array $tags = [];

    /**
     * Register a custom tag function.
     */
    public function register(string $name, callable $callback): void
    {
        $this->tags[$name] = $callback;
    }

    /**
     * Check if a tag is registered.
     */
    public function has(string $name): bool
    {
        return isset($this->tags[$name]);
    }

    /**
     * Call a tag function by name.
     *
     * @param string $name Function name (e.g. 'base_url', 'lang')
     * @param array  $args Resolved argument values
     * @return string Output string
     */
    public function call(string $name, array $args): string
    {
        if (isset($this->tags[$name])) {
            return (string) ($this->tags[$name])(...$args);
        }

        return '';
    }
}
