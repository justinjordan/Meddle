<?php

namespace Sxule\Meddle\Transpiler;

use Sxule\Meddle\Parser;
use Sxule\Meddle\Transpiler\BaseAttribute;
use Sxule\Meddle\Transpiler\AttributeInterface;
use DOMNode;

class ForAttribute extends BaseAttribute implements AttributeInterface
{
    /**
     * Reads Meddle syntax and adds appropriate PHP
     *
     * @param DOMNode $node
     * @return void
     */
    public static function transpileNode(DOMNode $node)
    {
        $type = 'for';
        $attr = 'mdl-for';

        $statement = $node->getAttribute($attr);
        $node->removeAttribute($attr);

        /** handle for in syntax */
        if (preg_match('/([a-z_][a-z0-9_]*)[\s]*in[\s]*([a-z_][a-z0-9_]*)/i', $statement)) {
            list($alias, $variable) = preg_split('/[\s]*in[\s]*/i', $statement);
            $type = 'foreach';
            $statement = "$variable as $alias";
        }

        $statement = Parser::decorateVariables($statement);

        self::wrapWithTags($node, "{? $type ($statement): ?}", "{? end$type; ?}");
    }
}
