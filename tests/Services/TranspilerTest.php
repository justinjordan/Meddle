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
    <p>{{ message }}</p>
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

        $output = (new Transpiler())->transpile($input);

        $this->assertEquals($this->cleanWhiteSpace($expectedOutput), $this->cleanWhiteSpace($output));
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

        $output = (new Transpiler())->transpile($input);

        $this->assertEquals($this->cleanWhiteSpace($expectedOutput), $this->cleanWhiteSpace($output));
    }

    public function testForeachAttributes()
    {
        $input = '<!DOCTYPE html>
<html>
<body>
        <p mdl-foreach="[1,2,3] as number">{{ number }}</p>
</body>
</html>';

        $expectedOutput = '<!DOCTYPE html>
<html>
<body>
        <?php foreach ([1,2,3] as $number): ?><p><?php echo $number; ?></p><?php endforeach; ?>
</body>
</html>';

        $output = (new Transpiler())->transpile($input);

        $this->assertEquals($this->cleanWhiteSpace($expectedOutput), $this->cleanWhiteSpace($output));
    }

    public function testForAttributes()
    {
        $input = '<!DOCTYPE html>
<html>
<body>
        <p mdl-for="i = 1; i <= 3; i++">{{ i }}</p>
</body>
</html>';

        $expectedOutput = '<!DOCTYPE html>
<html>
<body>
        <?php for ($i = 1; $i <= 3; $i++): ?><p><?php echo $i; ?></p><?php endfor; ?>
</body>
</html>';

        $output = (new Transpiler())->transpile($input);

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