<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

/** {% block name %}...{% endblock %} */
class BlockNode extends Node
{
    /** @param Node[] $body */
    public function __construct(
        public readonly string $name,
        public readonly array $body,
    ) {}
}
