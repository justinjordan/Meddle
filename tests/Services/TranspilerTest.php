<?php

namespace Meddle\Tests;

use PHPUnit\Framework\TestCase;
use Meddle\Services\Transpiler;

class TranspilerTest extends TestCase
{
    public function testTranspile()
    {
        $input = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Meddle Test</title>
</head>
<body>
    <p>{{ \'Hello, world!\' }}</p>
    <p>{{ $message }}</p>
    <p>{{ toUpper("all caps!") }}</p>
</body>
</html>';

        $expectedOutput = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Meddle Test</title>
</head>
<body>
    <p><?php echo \'Hello, world!\'; ?></p>
    <p><?php echo $message; ?></p>
    <p><?php echo $toUpper("all caps!"); ?></p>
</body>
</html>';

        $output = trim(Transpiler::transpile($input));

        $this->assertEquals($output, $expectedOutput);
    }
}