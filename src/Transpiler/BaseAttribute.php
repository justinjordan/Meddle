<?php

namespace Sxule\Meddle\Transpiler;

use DOMNode;

class BaseAttribute
{
    /**
     * Wraps DOMNode with pseudo tags
     *
     * @param DOMNode $node
     * @param string $openTag
     * @param string $closeTag
     * @return void
     */
    protected static function wrapWithTags(DOMNode $node, string $openTag, string $closeTag)
    {
        $doc = $node->ownerDocument;

        $openNode = $doc->createTextNode($openTag);
        $closeNode = $doc->createTextNode("$closeTag\n");

        $parent = $node->parentNode;
        $parent->insertBefore($openNode, $node);
        if ($node->nextSibling) {
            $parent->insertBefore($closeNode, $node->nextSibling);
        } else {
            $parent->appendChild($closeNode);
        }
    }
}
