<?php

namespace Meddle\Tests;

use PHPUnit\Framework\TestCase;
use Meddle\Document;

class DocumentTest extends TestCase
{
    public function testRender()
    {
        $output = Document::render(__DIR__.'/resources/DocumentTest_testRender.html', [
            'message'    => 'Hello, world!'
        ]);
        $output = trim($output);
        
        $expectedOutput = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Meddle Test</title>
</head>
<body>
    <p>Hello, world!</p>
    <p>Hello, world!</p>
</body>
</html>';
        
        $this->assertEquals($output, $expectedOutput);
    }
}
