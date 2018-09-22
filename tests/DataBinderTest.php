<?php

namespace Sxule\Meddle\Tests;

use PHPUnit\Framework\TestCase;
use Sxule\Meddle\DataBinder;

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

        $phpDocPath = __DIR__ . '/resources/DataBinderTest_testBind.php';

        $output = (new DataBinder())->bind($phpDocPath, [
            'message'   => 'Hello, world!'
        ]);

        $this->assertEquals($this->cleanWhiteSpace($expectedOutput), $this->cleanWhiteSpace($output));
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