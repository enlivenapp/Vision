<?php

/**
 * @package   Enlivenapp\Vision
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

namespace Enlivenapp\Vision;

/**
 * Recursive descent parser for expressions.
 *
 * Precedence (low -> high):
 *   or -> and -> not -> comparison -> filter (pipe) -> primary
 */
class ExpressionParser
{
    private array $tokens;
    private int $pos = 0;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function parse(): Expression
    {
        $expr = $this->parseOr();

        // If there are leftover tokens, that's fine — caller may not consume all
        return $expr;
    }

    private function current(): ?array
    {
        return $this->tokens[$this->pos] ?? null;
    }

    private function advance(): array
    {
        return $this->tokens[$this->pos++];
    }

    private function expect(string $type): array
    {
        $tok = $this->current();
        if ($tok === null || $tok['type'] !== $type) {
            // Return a dummy to avoid crashing — unknown vars silently output nothing
            return ['type' => $type, 'value' => ''];
        }
        return $this->advance();
    }

    private function parseOr(): Expression
    {
        $left = $this->parseAnd();

        while ($this->current() && $this->current()['type'] === 'KEYWORD' && $this->current()['value'] === 'or') {
            $this->advance();
            $right = $this->parseAnd();
            $left = new BooleanExpression($left, 'or', $right);
        }

        return $left;
    }

    private function parseAnd(): Expression
    {
        $left = $this->parseNot();

        while ($this->current() && $this->current()['type'] === 'KEYWORD' && $this->current()['value'] === 'and') {
            $this->advance();
            $right = $this->parseNot();
            $left = new BooleanExpression($left, 'and', $right);
        }

        return $left;
    }

    private function parseNot(): Expression
    {
        if ($this->current() && $this->current()['type'] === 'KEYWORD' && $this->current()['value'] === 'not') {
            $this->advance();
            return new NotExpression($this->parseNot());
        }

        return $this->parseComparison();
    }

    private function parseComparison(): Expression
    {
        $left = $this->parseFilter();

        if ($this->current() && $this->current()['type'] === 'OPERATOR') {
            $op = $this->advance()['value'];
            $right = $this->parseFilter();
            return new ComparisonExpression($left, $op, $right);
        }

        return $left;
    }

    private function parseFilter(): Expression
    {
        $expr = $this->parsePrimary();

        while ($this->current() && $this->current()['type'] === 'PIPE') {
            $this->advance(); // consume |
            $filterName = $this->expect('IDENTIFIER')['value'];
            $args = [];

            // Optional arguments in parentheses
            if ($this->current() && $this->current()['type'] === 'LPAREN') {
                $this->advance(); // consume (
                while ($this->current() && $this->current()['type'] !== 'RPAREN') {
                    $args[] = $this->parsePrimary();
                    if ($this->current() && $this->current()['type'] === 'COMMA') {
                        $this->advance();
                    }
                }
                if ($this->current()) {
                    $this->advance(); // consume )
                }
            }

            $expr = new FilterExpression($expr, $filterName, $args);
        }

        return $expr;
    }

    private function parsePrimary(): Expression
    {
        $tok = $this->current();

        if ($tok === null) {
            return new LiteralExpression('');
        }

        // String literal
        if ($tok['type'] === 'STRING') {
            $this->advance();
            return new LiteralExpression($tok['value']);
        }

        // Number literal
        if ($tok['type'] === 'NUMBER') {
            $this->advance();
            $val = str_contains($tok['value'], '.') ? (float) $tok['value'] : (int) $tok['value'];
            return new LiteralExpression($val);
        }

        // Boolean / null literals
        if ($tok['type'] === 'KEYWORD') {
            if ($tok['value'] === 'true') {
                $this->advance();
                return new LiteralExpression(true);
            }
            if ($tok['value'] === 'false') {
                $this->advance();
                return new LiteralExpression(false);
            }
            if ($tok['value'] === 'null') {
                $this->advance();
                return new LiteralExpression(null);
            }
        }

        // Grouped expression
        if ($tok['type'] === 'LPAREN') {
            $this->advance();
            $expr = $this->parseOr();
            $this->expect('RPAREN');
            return $expr;
        }

        // Variable (possibly dotted)
        if ($tok['type'] === 'IDENTIFIER') {
            $parts = [$this->advance()['value']];
            while ($this->current() && $this->current()['type'] === 'DOT') {
                $this->advance(); // consume .
                $parts[] = $this->expect('IDENTIFIER')['value'];
            }
            return new VariableExpression($parts);
        }

        // Fallback
        $this->advance();
        return new LiteralExpression('');
    }
}
