<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

/** {! expression !} — raw (unescaped) output. */
class RawOutputNode extends Node
{
    public function __construct(public readonly Expression $expression) {}
}
