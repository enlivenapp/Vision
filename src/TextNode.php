<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

/** Literal text content — output as-is. */
class TextNode extends Node
{
    public function __construct(public readonly string $text) {}
}
