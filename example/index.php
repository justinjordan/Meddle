<?php

require '../src/autoload.php';

Meddle\Document::render('template.html', [
    'secretMessage'    => 'Hello, world!'
]);
