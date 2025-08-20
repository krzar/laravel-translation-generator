<?php

namespace Krzar\LaravelTranslationGenerator\Tests\Unit\Services\Finders;

use Krzar\LaravelTranslationGenerator\Services\Finders\LanguagesFinder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LanguagesFinderTest extends TestCase
{
    private LanguagesFinder $finder;

    private string $tempLangPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempLangPath = sys_get_temp_dir().'/test_lang_'.uniqid();
        mkdir($this->tempLangPath);

        $GLOBALS['tempLangPath'] = $this->tempLangPath;

        $this->finder = new LanguagesFinder;
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempLangPath)) {
            $this->removeDirectory($this->tempLangPath);
        }
        parent::tearDown();
    }

    #[Test]
    public function finds_existing_language_directories(): void
    {
        mkdir($this->tempLangPath.'/en');
        mkdir($this->tempLangPath.'/pl');
        mkdir($this->tempLangPath.'/de');

        file_put_contents($this->tempLangPath.'/en/messages.php', '<?php return [];');
        file_put_contents($this->tempLangPath.'/pl/messages.php', '<?php return [];');
        file_put_contents($this->tempLangPath.'/de/messages.php', '<?php return [];');

        $result = $this->finder->getAvailableLanguages();

        $this->assertCount(3, $result);
        $this->assertTrue($result->offsetExists('en'));
        $this->assertTrue($result->offsetExists('pl'));
        $this->assertTrue($result->offsetExists('de'));
    }

    #[Test]
    public function ignores_system_directories(): void
    {
        mkdir($this->tempLangPath.'/en');
        file_put_contents($this->tempLangPath.'/en/messages.php', '<?php return [];');

        touch($this->tempLangPath.'/some_file.txt');

        $result = $this->finder->getAvailableLanguages();

        $this->assertCount(1, $result);
        $this->assertTrue($result->offsetExists('en'));
        $this->assertFalse($result->contains('some_file.txt'));
    }

    #[Test]
    public function returns_empty_collection_when_no_language_directories(): void
    {
        touch($this->tempLangPath.'/some_file.txt');

        $result = $this->finder->getAvailableLanguages();

        $this->assertCount(0, $result);
    }

    #[Test]
    public function keys_and_values_are_identical(): void
    {
        mkdir($this->tempLangPath.'/en');
        mkdir($this->tempLangPath.'/pl');
        file_put_contents($this->tempLangPath.'/en/messages.php', '<?php return [];');
        file_put_contents($this->tempLangPath.'/pl/messages.php', '<?php return [];');

        $result = $this->finder->getAvailableLanguages();

        $result->each(function ($value, $key) {
            $this->assertEquals($key, $value, "Key '$key' should equal value '$value'");
        });
    }

    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.DIRECTORY_SEPARATOR.$file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
