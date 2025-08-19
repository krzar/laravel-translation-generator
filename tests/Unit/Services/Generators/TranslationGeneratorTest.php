<?php

namespace Krzar\LaravelTranslationGenerator\Tests\Unit\Services\Generators;

use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Exceptions\FallbackLanguageFileNotExistsException;
use Krzar\LaravelTranslationGenerator\Services\Generators\TranslationGenerator;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TranslationGeneratorTest extends TestCase
{
    private TranslationGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new class('en', 'pl', false, false, false) extends TranslationGenerator
        {
            public function generate(): void {}

            protected function putToFile(Collection $translations): void {}

            protected function getTranslations(string $locale): ?Collection
            {
                return match ($locale) {
                    'pl' => collect(['hello' => 'cześć', 'goodbye' => 'do widzenia']),
                    'en' => collect(['hello' => 'hello', 'new_key' => 'new value']),
                    default => null
                };
            }
        };
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function constructor_sets_properties_correctly(): void
    {
        $generator = new class('en', 'pl', true, true, true) extends TranslationGenerator
        {
            public function generate(): void {}

            protected function putToFile(Collection $translations): void {}

            protected function getTranslations(string $locale): ?Collection
            {
                return collect();
            }

            public function getLang(): string
            {
                return $this->lang;
            }

            public function getFallback(): string
            {
                return $this->fallback;
            }

            public function getOverwrite(): bool
            {
                return $this->overwrite;
            }

            public function getClearValues(): bool
            {
                return $this->clearValues;
            }

            public function getGeneratePackagesTranslations(): bool
            {
                return $this->generatePackagesTranslations;
            }
        };

        $this->assertEquals('en', $generator->getLang());
        $this->assertEquals('pl', $generator->getFallback());
        $this->assertTrue($generator->getOverwrite());
        $this->assertTrue($generator->getClearValues());
        $this->assertTrue($generator->getGeneratePackagesTranslations());
    }

    #[Test]
    #[DataProvider('generateSingleDataProvider')]
    public function generate_single_works_correctly(
        string $lang,
        string $fallback,
        bool $overwrite,
        bool $clearValues,
        ?Collection $expectedResult
    ): void {
        $generator = new class($lang, $fallback, $overwrite, $clearValues, false) extends TranslationGenerator
        {
            public ?Collection $putToFileResult = null;

            public function generate(): void {}

            protected function putToFile(Collection $translations): void
            {
                $this->putToFileResult = $translations;
            }

            protected function getTranslations(string $locale): ?Collection
            {
                return match ($locale) {
                    'pl' => collect(['hello' => 'cześć', 'goodbye' => 'do widzenia']),
                    'en' => collect(['hello' => 'hello', 'new_key' => 'new value']),
                    'de' => null,
                    default => collect()
                };
            }

            public function callGenerateSingle(): void
            {
                $this->generateSingle();
            }
        };

        if ($expectedResult === null) {
            $this->expectException(FallbackLanguageFileNotExistsException::class);
        }

        $generator->callGenerateSingle();

        if ($expectedResult !== null) {
            $this->assertEquals($expectedResult->toArray(), $generator->putToFileResult->toArray());
        }
    }

    public static function generateSingleDataProvider(): array
    {
        return [
            'no overwrite with existing translations' => [
                'lang' => 'en',
                'fallback' => 'pl',
                'overwrite' => false,
                'clearValues' => false,
                'expectedResult' => collect(['hello' => 'hello', 'goodbye' => 'do widzenia']),
            ],
            'overwrite with clear values' => [
                'lang' => 'en',
                'fallback' => 'pl',
                'overwrite' => true,
                'clearValues' => true,
                'expectedResult' => collect(['hello' => '', 'goodbye' => '']),
            ],
            'overwrite without clear values' => [
                'lang' => 'en',
                'fallback' => 'pl',
                'overwrite' => true,
                'clearValues' => false,
                'expectedResult' => collect(['hello' => 'cześć', 'goodbye' => 'do widzenia']),
            ],
            'fallback language not exists' => [
                'lang' => 'en',
                'fallback' => 'de',
                'overwrite' => false,
                'clearValues' => false,
                'expectedResult' => null,
            ],
        ];
    }

    #[Test]
    public function generate_single_throws_exception_when_fallback_language_not_exists(): void
    {
        $generator = new class('en', 'nonexistent', false, false, false) extends TranslationGenerator
        {
            public function generate(): void {}

            protected function putToFile(Collection $translations): void {}

            protected function getTranslations(string $locale): ?Collection
            {
                return $locale === 'nonexistent' ? null : collect();
            }

            public function callGenerateSingle(): void
            {
                $this->generateSingle();
            }
        };

        $this->expectException(FallbackLanguageFileNotExistsException::class);
        $this->expectExceptionMessage("File 'nonexistent.json' not exists for fallback 'nonexistent' language.");

        $generator->callGenerateSingle();
    }
}
