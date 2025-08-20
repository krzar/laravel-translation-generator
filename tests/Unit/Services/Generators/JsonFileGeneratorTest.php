<?php

namespace Krzar\LaravelTranslationGenerator\Tests\Unit\Services\Generators;

use Krzar\LaravelTranslationGenerator\Services\Generators\JsonFileGenerator;
use Krzar\LaravelTranslationGenerator\Services\PackagesTranslationsService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JsonFileGeneratorTest extends TestCase
{
    private string $tempLangPath;

    private PackagesTranslationsService $packagesService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempLangPath = sys_get_temp_dir().'/test_lang_'.uniqid();
        mkdir($this->tempLangPath);

        $GLOBALS['tempLangPath'] = $this->tempLangPath;

        $this->packagesService = Mockery::mock(PackagesTranslationsService::class);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempLangPath)) {
            $this->removeDirectory($this->tempLangPath);
        }
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function generates_json_translation_file_from_fallback(): void
    {

        $fallbackTranslations = [
            'welcome' => 'Welcome',
            'goodbye' => 'Goodbye',
        ];

        file_put_contents(
            $this->tempLangPath.'/pl.json',
            json_encode($fallbackTranslations, JSON_PRETTY_PRINT)
        );

        $this->packagesService->shouldReceive('findPackages')
            ->andReturn(collect());

        $generator = new JsonFileGenerator('en', 'pl', false, false, false, $this->packagesService);
        $generator->generate();

        $this->assertFileExists($this->tempLangPath.'/en.json');

        $generatedContent = json_decode(
            file_get_contents($this->tempLangPath.'/en.json'),
            true
        );

        $this->assertEquals($fallbackTranslations, $generatedContent);
    }

    #[Test]
    public function skips_generation_when_no_json_files_exist(): void
    {

        $this->packagesService->shouldReceive('findPackages')
            ->andReturn(collect());

        $generator = new JsonFileGenerator('en', 'pl', false, false, false, $this->packagesService);
        $generator->generate();

        $this->assertFileDoesNotExist($this->tempLangPath.'/en.json');
    }

    #[Test]
    public function merges_translations_when_target_file_exists(): void
    {

        $fallbackTranslations = [
            'welcome' => 'Welcome',
            'goodbye' => 'Goodbye',
            'new_key' => 'New value',
        ];

        $existingTranslations = [
            'welcome' => 'Hello',
            'existing' => 'Existing value',
        ];

        file_put_contents(
            $this->tempLangPath.'/pl.json',
            json_encode($fallbackTranslations, JSON_PRETTY_PRINT)
        );

        file_put_contents(
            $this->tempLangPath.'/en.json',
            json_encode($existingTranslations, JSON_PRETTY_PRINT)
        );

        $this->packagesService->shouldReceive('findPackages')
            ->andReturn(collect());

        $generator = new JsonFileGenerator('en', 'pl', false, false, false, $this->packagesService);
        $generator->generate();

        $generatedContent = json_decode(
            file_get_contents($this->tempLangPath.'/en.json'),
            true
        );

        $this->assertEquals('Hello', $generatedContent['welcome']);
        $this->assertArrayNotHasKey('existing', $generatedContent);
        $this->assertEquals('New value', $generatedContent['new_key']);
        $this->assertEquals('Goodbye', $generatedContent['goodbye']);
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
