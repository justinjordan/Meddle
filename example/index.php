<?php

require '../autoload.php';

$startTime = microtime(true);

$markup = (new Sxule\Meddle\Document())->render('template.html', [
    'subheading'    => 'This is a demonstration of how the FizzBuzz problem looks in Meddle.',
    'names'         => [
        'First'         => 'Frank',
        'Middle'        => 'William',
        'Last'          => 'Abagnale',
    ],
], [
    'devMode'   => true
]);

$duration = 1000 * (microtime(true) - $startTime);
$markup = str_replace('{%duration%}', number_format($duration, 4)." milliseconds", $markup);

echo $markup;
