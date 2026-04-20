<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

/** left == right, left > right, etc. */
class ComparisonExpression extends Expression
{
    public function __construct(
        public readonly Expression $left,
        public readonly string $operator,
        public readonly Expression $right,
    ) {}
}
