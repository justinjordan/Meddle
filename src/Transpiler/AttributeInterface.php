<?php

namespace Sxule\Meddle\Transpiler;

use DOMNode;

interface AttributeInterface
{
    public static function transpileNode(DOMNode $node);
}
