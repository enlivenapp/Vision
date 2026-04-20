<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

/** String, number, bool, or null literal. */
class LiteralExpression extends Expression
{
    public function __construct(public readonly mixed $value) {}
}
