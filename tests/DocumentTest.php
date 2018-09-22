<?php

namespace Sxule\Meddle\Tests;

use PHPUnit\Framework\TestCase;
use Sxule\Meddle\Document;

class DocumentTest extends TestCase
{
    public function testRender()
    {
        $output = (new Document())->render(__DIR__ . '/resources/DocumentTest_testRender.html', [
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
    <p>Single quotes</p>
    <p>Double quotes</p>
    <p>\' escaping</p>
    <p>" escaping</p>
    <p>Hello, world!</p>
    <p>FUNCTIONS</p>
</body>
</html>';
        
        $this->assertEquals($expectedOutput, $output);
    }

    public function testPHPRemoval()
    {
        $output = (new Document())->render(__DIR__ . '/resources/DocumentTest_testPHPRemoval.html');
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

</body>
</html>';

        $this->assertEquals($expectedOutput, $output);
    }
}
