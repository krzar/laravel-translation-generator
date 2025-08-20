<?php

namespace Krzar\LaravelTranslationGenerator\Tests\Unit\Services;

use Krzar\LaravelTranslationGenerator\Services\Finders\LanguagesFinder;
use Krzar\LaravelTranslationGenerator\Services\Generators\PhpFileGenerator;
use Krzar\LaravelTranslationGenerator\Services\MakeTranslationFileService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MakeTranslationFileServiceTest extends TestCase
{
    private MakeTranslationFileService $service;

    private PhpFileGenerator $phpFileGenerator;

    private LanguagesFinder $languagesFinder;

    private string $tempLangPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempLangPath = sys_get_temp_dir().'/test_lang_'.uniqid();
        mkdir($this->tempLangPath);

        if (! function_exists('lang_path')) {
            function lang_path($path = '')
            {
                global $tempLangPath;

                return $tempLangPath.($path ? DIRECTORY_SEPARATOR.$path : '');
            }
        }

        $GLOBALS['tempLangPath'] = $this->tempLangPath;

        $this->phpFileGenerator = Mockery::mock(PhpFileGenerator::class);
        $this->languagesFinder = Mockery::mock(LanguagesFinder::class);

        $this->service = new MakeTranslationFileService(
            $this->phpFileGenerator,
            $this->languagesFinder
        );
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
    public function generates_files_for_provided_languages(): void
    {
        $languages = collect(['en', 'pl', 'de']);
        $fileName = 'messages';
        $fileContent = '<?php return [];';

        mkdir($this->tempLangPath.'/en');
        mkdir($this->tempLangPath.'/pl');
        mkdir($this->tempLangPath.'/de');

        $this->phpFileGenerator->shouldReceive('parseContent')
            ->times(3)
            ->andReturn($fileContent);

        $this->service->generate($fileName, $languages);

        $this->assertFileExists($this->tempLangPath.'/en/messages.php');
        $this->assertFileExists($this->tempLangPath.'/pl/messages.php');
        $this->assertFileExists($this->tempLangPath.'/de/messages.php');

        $this->assertEquals($fileContent, file_get_contents($this->tempLangPath.'/en/messages.php'));
    }

    #[Test]
    public function uses_all_available_languages_when_empty_collection_provided(): void
    {
        $availableLanguages = collect(['fr', 'es']);
        $fileName = 'common';
        $fileContent = '<?php return [];';

        mkdir($this->tempLangPath.'/fr');
        mkdir($this->tempLangPath.'/es');

        $this->languagesFinder->shouldReceive('getAvailableLanguages')
            ->once()
            ->andReturn($availableLanguages);

        $this->phpFileGenerator->shouldReceive('parseContent')
            ->times(2)
            ->andReturn($fileContent);

        $this->service->generate($fileName, collect());

        $this->assertFileExists($this->tempLangPath.'/fr/common.php');
        $this->assertFileExists($this->tempLangPath.'/es/common.php');
    }

    #[Test]
    public function creates_file_with_correct_content(): void
    {
        $languages = collect(['en']);
        $fileName = 'validation';
        $expectedContent = "<?php return ['required' => 'Field is required'];";

        mkdir($this->tempLangPath.'/en');

        $this->phpFileGenerator->shouldReceive('parseContent')
            ->once()
            ->andReturn($expectedContent);

        $this->service->generate($fileName, $languages);

        $actualContent = file_get_contents($this->tempLangPath.'/en/validation.php');
        $this->assertEquals($expectedContent, $actualContent);
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
