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

        // init renderer
        $this->meddle = new Meddle();
    }

    public function testRender()
    {
        $output = $this->meddle->render(__DIR__ . '/resources/DocumentTest_testRender.html', [
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

    public function testAttributeInterpolation()
    {
        $output = $this->meddle->render('<div data-test="{{ message }}"></div>', [
            'message'   => 'hello',
        ]);

        $expectedOutput = '<div data-test="hello"></div>';

        $this->assertEquals($expectedOutput, $output);
    }

    public function testMdlIgnore()
    {
        // Test single level
        $output = $this->meddle->render('<div mdl-ignore>{{ message }}</div>', [
            'message'   => 'hello',
        ]);

        $expectedOutput = '<div>{{ message }}</div>';

        $this->assertEquals($expectedOutput, $output);

        // Test multi-level
        $output = $this->meddle->render('<div mdl-ignore><p>{{ message }}</p></div>', [
            'message'   => 'hello',
        ]);

        $expectedOutput = '<div><p>{{ message }}</p></div>';

        $this->assertEquals($expectedOutput, $output);
    }

    public function testPHPRemoval()
    {
        $output = $this->meddle->render(__DIR__ . '/resources/DocumentTest_testPHPRemoval.html');
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
        $actual = $this->meddle->render($input, $data);

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
        $actual = $this->meddle->render($input, $data);

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
        $actual = $this->meddle->render($input, $data);

        $this->assertEquals($expected, $actual);
    }
}
