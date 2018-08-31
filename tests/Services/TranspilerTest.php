<?php

namespace Meddle\Tests;

use PHPUnit\Framework\TestCase;
use Meddle\Services\Transpiler;

class TranspilerTest extends TestCase
{
    public function testMustacheTags()
    {
        $input = '<!DOCTYPE html>
<html>
<body>
    <p>{{ \'Hello, world!\' }}</p>
    <p>{{ $message }}</p>
    <p>{{ toUpper("all caps!") }}</p>
</body>
</html>';

        $expectedOutput = '<!DOCTYPE html>
<html>
<body>
    <p><?php echo \'Hello, world!\'; ?></p>
    <p><?php echo $message; ?></p>
    <p><?php echo $toUpper("all caps!"); ?></p>
</body>
</html>';

        $output = Transpiler::transpile($input);

        $this->assertEquals($this->cleanWhiteSpace($output), $this->cleanWhiteSpace($expectedOutput));
    }

    public function testIfAttributes()
    {
        $input = '<!DOCTYPE html>
<html>
<body>
        <p mdl-if="true">bing</p>
        <p mdl-if="false">bing</p>
</body>
</html>';

        $expectedOutput = '<!DOCTYPE html>
<html>
<body>
        <?php if (true): ?><p>bing</p><?php endif; ?>
        <?php if (false): ?><p>bing</p><?php endif; ?>
</body>
</html>';

        $output = Transpiler::transpile($input);

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