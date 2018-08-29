<?php

require '../autoload.php';

echo Meddle\Document::render('template.html', [
    'secretMessage'    => 'Hello, world!'
], [
    'devMode'   => true
]);
