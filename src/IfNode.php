<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

/** {% if %}...{% elseif %}...{% else %}...{% endif %} */
class IfNode extends Node
{
    /**
     * @param array<array{condition: Expression, body: Node[]}> $branches
     * @param Node[]|null $elseBody
     */
    public function __construct(
        public readonly array $branches,
        public readonly ?array $elseBody = null,
    ) {}
}
