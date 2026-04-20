<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

/** not expression */
class NotExpression extends Expression
{
    public function __construct(public readonly Expression $expression) {}
}
