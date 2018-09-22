<?php

namespace Sxule\Meddle\Transpiler;

use Sxule\Meddle\Parser;
use Sxule\Meddle\Transpiler\BaseAttribute;
use Sxule\Meddle\Transpiler\AttributeInterface;
use DOMNode;

class IfAttribute extends BaseAttribute implements AttributeInterface
{
    /**
     * Reads Meddle syntax and adds appropriate PHP
     *
     * @param DOMNode $node
     * @return void
     */
    public static function transpileNode(DOMNode $node)
    {
        $attr = 'mdl-if';

        $statement = $node->getAttribute($attr);
        $node->removeAttribute($attr);

        $statement = Parser::decorateVariables($statement);

        self::wrapWithTags($node, "{? if ($statement): ?}", "{? endif; ?}");
    }
}
