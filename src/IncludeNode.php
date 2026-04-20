<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

/** {% include 'partial' with {key: value} %} */
class IncludeNode extends Node
{
    /** @param array<string, Expression>|null $withData */
    public function __construct(
        public readonly string $templateName,
        public readonly ?array $withData = null,
    ) {}
}
