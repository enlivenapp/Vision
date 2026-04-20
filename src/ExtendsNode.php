<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

/** {% extends 'layout' %} */
class ExtendsNode extends Node
{
    public function __construct(public readonly string $templateName) {}
}
