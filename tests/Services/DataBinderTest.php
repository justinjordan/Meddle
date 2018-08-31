<?php

namespace Meddle\Tests;

use PHPUnit\Framework\TestCase;
use Meddle\Services\DataBinder;

class DataBinderTest extends TestCase
{
    public function testBind()
    {
       $expectedOutput = '<!DOCTYPE html>
<html>
<body>
    <p>Hello, world!</p>
</body>
</html>
';

        $phpDocPath = dirname(__DIR__).'/resources/DataBinderTest_testBind.php';
        $output = DataBinder::bind($phpDocPath, [
            'message'   => 'Hello, world!'
        ]);

        $this->assertEquals($this->cleanWhiteSpace($output), $this->cleanWhiteSpace($expectedOutput));
    }

    private function cleanWhiteSpace($input)
    {
        $output = $input;

        $output = str_replace("\n", '', $output);
        $output = str_replace("\t", '', $output);
        $output = trim($output);

        return $output;
    }
}