<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

class FilterRegistry
{
    /** @var array<string, callable> */
    private array $filters = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    /**
     * Register a custom filter.
     */
    public function register(string $name, callable $callback): void
    {
        $this->filters[$name] = $callback;
    }

    /**
     * Apply a named filter to a value.
     *
     * Unknown filters return the input unchanged.
     */
    public function apply(string $name, mixed $value, array $args = []): mixed
    {
        if ($name === 'raw') {
            return $value;
        }

        if (isset($this->filters[$name])) {
            return ($this->filters[$name])($value, ...$args);
        }

        return $value;
    }

    /**
     * Check if a filter name is the 'raw' marker.
     */
    public function isRawFilter(string $name): bool
    {
        return $name === 'raw';
    }

    private function registerDefaults(): void
    {
        $this->filters['date'] = function (mixed $value, string $format = 'Y-m-d'): string {
            $timestamp = is_numeric($value) ? (int) $value : strtotime((string) $value);
            if ($timestamp === false) {
                return (string) $value;
            }
            return date($format, $timestamp);
        };

        $this->filters['number_format'] = function (mixed $value, int $decimals = 0): string {
            return number_format((float) $value, $decimals);
        };

        $this->filters['nl2br'] = fn(mixed $value): string => nl2br((string) $value);

        $this->filters['md5'] = fn(mixed $value): string => md5((string) $value);

        $this->filters['count'] = fn(mixed $value): int => is_countable($value) ? count($value) : 0;

        $this->filters['upper'] = fn(mixed $value): string => mb_strtoupper((string) $value);

        $this->filters['lower'] = fn(mixed $value): string => mb_strtolower((string) $value);

        $this->filters['strip_tags'] = fn(mixed $value): string => strip_tags((string) $value);

        $this->filters['excerpt'] = function (mixed $value, int $length = 150): string {
            $plain = strip_tags((string) $value);
            if (strlen($plain) <= $length) {
                return $plain;
            }
            return rtrim(substr($plain, 0, $length), ' .,;:') . '…';
        };

        $this->filters['default'] = function (mixed $value, mixed $default = ''): mixed {
            if ($value === null || $value === '' || $value === false) {
                return $default;
            }
            return $value;
        };
    }
}
