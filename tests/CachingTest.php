<?php

namespace Sxule\Meddle\Tests;

use PHPUnit\Framework\TestCase;
use Sxule\Meddle\Caching;

class CachingTest extends TestCase
{
    private static $filePath;

    public function testSaveFile()
    {
        Caching::setCacheDirectory(__DIR__.'/cache');

        $fileContents = '<?php echo "This is a PHP file."; ?>';
        $filePath = Caching::saveFile('testfile', 'php', $fileContents);

        /** File was made */
        $this->assertTrue(file_exists($filePath));

        $actualFileContents = file_get_contents($filePath);

        /** File contents were added */
        $this->assertEquals($fileContents, $actualFileContents);

        self::$filePath = $filePath;
    }

    /**
     * @depends testSaveFile
     */
    public function testRemoveFile()
    {
        $filePath = self::$filePath;

        $removalSuccess = Caching::removeFile('testfile', 'php');

        /** Caching says file is gone */
        $this->assertTrue($removalSuccess);

        /** File actually gone */
        $this->assertFalse(file_exists($filePath));
    }
}
