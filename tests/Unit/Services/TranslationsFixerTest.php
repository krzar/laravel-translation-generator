<?php

namespace Krzar\LaravelTranslationGenerator\Tests\Unit\Services;

use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Services\TranslationsFixer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TranslationsFixerTest extends TestCase
{
    #[Test]
    #[DataProvider('fixToEmptyDataProvider')]
    public function fix_to_empty_returns_empty_values(Collection $input, Collection $expected): void
    {
        $result = TranslationsFixer::fixToEmpty($input);

        $this->assertEquals($expected->toArray(), $result->toArray());
    }

    public static function fixToEmptyDataProvider(): array
    {
        return [
            'simple string values' => [
                'input' => collect(['hello' => 'world', 'foo' => 'bar']),
                'expected' => collect(['hello' => '', 'foo' => '']),
            ],
            'nested array values' => [
                'input' => collect([
                    'auth' => [
                        'failed' => 'Login failed',
                        'throttle' => 'Too many attempts',
                    ],
                    'simple' => 'value',
                ]),
                'expected' => collect([
                    'auth' => collect([
                        'failed' => '',
                        'throttle' => '',
                    ]),
                    'simple' => '',
                ]),
            ],
            'deeply nested values' => [
                'input' => collect([
                    'level1' => [
                        'level2' => [
                            'level3' => 'deep value',
                        ],
                    ],
                ]),
                'expected' => collect([
                    'level1' => collect([
                        'level2' => collect([
                            'level3' => '',
                        ]),
                    ]),
                ]),
            ],
            'empty collection' => [
                'input' => collect([]),
                'expected' => collect([]),
            ],
        ];
    }

    #[Test]
    #[DataProvider('fixToOtherTranslationsDataProvider')]
    public function fix_to_other_translations_works_correctly(
        Collection $translations,
        Collection $otherTranslations,
        bool $clearIfNotExists,
        Collection $expected
    ): void {
        $result = TranslationsFixer::fixToOtherTranslations(
            $translations,
            $otherTranslations,
            $clearIfNotExists
        );

        $this->assertEquals($expected->toArray(), $result->toArray());
    }

    public static function fixToOtherTranslationsDataProvider(): array
    {
        return [
            'merge existing translations without clearing' => [
                'translations' => collect(['hello' => 'world', 'new' => 'value']),
                'otherTranslations' => collect(['hello' => 'existing']),
                'clearIfNotExists' => false,
                'expected' => collect(['hello' => 'existing', 'new' => 'value']),
            ],
            'merge existing translations with clearing' => [
                'translations' => collect(['hello' => 'world', 'new' => 'value']),
                'otherTranslations' => collect(['hello' => 'existing']),
                'clearIfNotExists' => true,
                'expected' => collect(['hello' => 'existing', 'new' => '']),
            ],
            'all keys exist in other translations' => [
                'translations' => collect(['hello' => 'world', 'goodbye' => 'bye']),
                'otherTranslations' => collect(['hello' => 'existing hello', 'goodbye' => 'existing goodbye']),
                'clearIfNotExists' => false,
                'expected' => collect(['hello' => 'existing hello', 'goodbye' => 'existing goodbye']),
            ],
            'no matching keys' => [
                'translations' => collect(['hello' => 'world', 'goodbye' => 'bye']),
                'otherTranslations' => collect(['different' => 'key']),
                'clearIfNotExists' => false,
                'expected' => collect(['hello' => 'world', 'goodbye' => 'bye']),
            ],
            'no matching keys with clearing' => [
                'translations' => collect(['hello' => 'world', 'goodbye' => 'bye']),
                'otherTranslations' => collect(['different' => 'key']),
                'clearIfNotExists' => true,
                'expected' => collect(['hello' => '', 'goodbye' => '']),
            ],
        ];
    }

    #[Test]
    #[DataProvider('fixToOtherTranslationSingleDataProvider')]
    public function fix_to_other_translation_single_works_correctly(
        string|array $translation,
        string|array|null $otherTranslation,
        bool $clearIfNotExists,
        string|array|Collection $expected
    ): void {
        $result = TranslationsFixer::fixToOtherTranslationSingle(
            $translation,
            $otherTranslation,
            $clearIfNotExists
        );

        if ($expected instanceof Collection) {
            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals($expected->toArray(), $result->toArray());
        } else {
            $this->assertEquals($expected, $result);
        }
    }

    public static function fixToOtherTranslationSingleDataProvider(): array
    {
        return [
            'string translation with other translation exists' => [
                'translation' => 'original',
                'otherTranslation' => 'replacement',
                'clearIfNotExists' => false,
                'expected' => 'replacement',
            ],
            'string translation with other translation null, no clear' => [
                'translation' => 'original',
                'otherTranslation' => null,
                'clearIfNotExists' => false,
                'expected' => 'original',
            ],
            'string translation with other translation null, with clear' => [
                'translation' => 'original',
                'otherTranslation' => null,
                'clearIfNotExists' => true,
                'expected' => '',
            ],
            'array translation returns collection' => [
                'translation' => ['key' => 'value'],
                'otherTranslation' => null,
                'clearIfNotExists' => false,
                'expected' => collect(['key' => 'value']),
            ],
            'string with array other translation' => [
                'translation' => 'original',
                'otherTranslation' => ['nested' => 'value'],
                'clearIfNotExists' => false,
                'expected' => collect(['nested' => 'value']),
            ],
        ];
    }

    #[Test]
    public function fix_to_empty_handles_empty_collection(): void
    {
        $result = TranslationsFixer::fixToEmpty(collect());

        $this->assertTrue($result->isEmpty());
    }

    #[Test]
    public function fix_to_other_translations_handles_empty_collections(): void
    {
        $result = TranslationsFixer::fixToOtherTranslations(
            collect(),
            collect(),
            false
        );

        $this->assertTrue($result->isEmpty());
    }

    #[Test]
    public function fix_to_other_translation_single_handles_empty_string(): void
    {
        $result = TranslationsFixer::fixToOtherTranslationSingle('', null, false);

        $this->assertEquals('', $result);
    }

    #[Test]
    public function fix_to_other_translation_single_handles_empty_array(): void
    {
        $result = TranslationsFixer::fixToOtherTranslationSingle([], null, false);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }
}
