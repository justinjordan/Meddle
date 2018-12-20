<?php

namespace Sxule\Meddle\Tests;

use PHPUnit\Framework\TestCase;
use Sxule\Meddle;

class MeddleTest extends TestCase
{
    public function setUp()
    {
        // remove cache directory
        $cacheDir = __DIR__ . '/cache';
        $cacheFiles = scandir($cacheDir);
        foreach ($cacheFiles as $file) {
            $path = $cacheDir . '/' . $file;
            
            if (is_dir($path)) {
                continue;
            }

            unlink($path);
        }
    }

    public function testRender()
    {
        $output = (new Meddle())->render(__DIR__ . '/resources/DocumentTest_testRender.html', [
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
        $output = (new Meddle())->render(__DIR__ . '/resources/DocumentTest_testPHPRemoval.html');
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

    public function testArrayReplacement()
    {
        /**
         * Test one level
         */
        $input = "<p>{{ myArray[0] }}</p>";
        $data = [
            'myArray' => ["Array contents."]
        ];

        $expected = "<p>" . $data['myArray'][0] . "</p>";
        $actual = (new Meddle())->render($input, $data);

        $this->assertEquals($expected, $actual);

        /**
         * Test two levels
         */
        $input = "<p>{{ level1.level2 }}</p>";
        $data = [
            'level1' => [
                'level2' => "Contents"
            ]
        ];

        $expected = "<p>" . $data['level1']['level2'] . "</p>";
        $actual = (new Meddle())->render($input, $data);

        $this->assertEquals($expected, $actual);

        /**
         * Test three levels with index
         */
        $input = "<p>{{ level1.level2[0] }}</p>";
        $data = [
            'level1' => [
                'level2' => [
                    "Contents"
                ]
            ]
        ];

        $expected = "<p>" . $data['level1']['level2'][0] . "</p>";
        $actual = (new Meddle())->render($input, $data);

        $this->assertEquals($expected, $actual);
    }
}
