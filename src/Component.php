<?php

namespace Sxule\Meddle;

abstract class Component
{
    protected $document;
    protected $props = [];
    public $components = [];

    abstract public function render(array $props);
}
