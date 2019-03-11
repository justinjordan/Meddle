<?php

namespace Sxule\Meddle\Transpiler;

use Sxule\Meddle\Parser;
use Sxule\Meddle\Transpiler\BaseAttribute;
use Sxule\Meddle\Transpiler\AttributeInterface;
use DOMNode;

class IgnoreAttribute extends BaseAttribute implements AttributeInterface
{
    /**
     * Reads Meddle syntax and adds appropriate PHP
     *
     * @param DOMNode $node
     * @return void
     */
    public static function transpileNode(DOMNode $node)
    {
        $node->removeAttribute('mdl-ignore');
    }
}
