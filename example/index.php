<?php

require '../autoload.php';

$startTime = microtime(true);
$markup = Meddle\Document::render('template.html', [
    'myMessage'     => 'Hello, world!',
], [
    'devMode'   => true
]);
$duration = 1000 * (microtime(true) - $startTime);

echo str_replace('{%duration%}', number_format($duration, 4)." milliseconds", $markup);
