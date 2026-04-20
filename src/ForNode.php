<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

/** {% for item in collection %}...{% endfor %} */
class ForNode extends Node
{
    /** @param Node[] $body */
    public function __construct(
        public readonly string $variableName,
        public readonly Expression $iterable,
        public readonly array $body,
    ) {}
}
