<?php

namespace Sxule\Meddle;

abstract class Component
{
    protected $document;
    protected $props;

    abstract public function render();

    public function __construct($node)
    {
        $this->document = $node->ownerDocument;
        $this->props = [];
        foreach ($node->attributes as $attr) {
            $this->props[$attr->nodeName] = $attr->nodeValue;
        }

        print_r($this->props);
        die();
    }
}
