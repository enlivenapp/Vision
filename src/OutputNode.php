<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

/** {{ expression }} — auto-escaped output. */
class OutputNode extends Node
{
    public function __construct(public readonly Expression $expression) {}
}
