<?php

namespace Meddle\example\components;

use Sxule\Meddle\Component;

class TestComponent extends Component
{
    public function render()
    {
        return '<p>This is a Meddle component!</p>';
    }
}
