<?php

namespace Krzar\LaravelTranslationGenerator\Tests\Unit\Services\Generators;

use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Services\Generators\PhpFileGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PhpFileGeneratorTest extends TestCase
{
    private PhpFileGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new PhpFileGenerator('en', 'pl');
    }

    #[Test]
    #[DataProvider('parseContentDataProvider')]
    public function parse_content_returns_correct_format(?Collection $translations, string $expected): void
    {
        $result = $this->generator->parseContent($translations);

        $this->assertEquals($expected, $result);
    }

    public static function parseContentDataProvider(): array
    {
        return [
            'null translations' => [
                'translations' => null,
                'expected' => "<?php\n\nreturn [\n];",
            ],
            'empty collection' => [
                'translations' => collect([]),
                'expected' => "<?php\n\nreturn [\n];",
            ],
            'simple translations' => [
                'translations' => collect(['hello' => 'cześć', 'goodbye' => 'do widzenia']),
                'expected' => "<?php\n\nreturn [\n\t\"hello\" => \"cześć\",\n\t\"goodbye\" => \"do widzenia\",\n];",
            ],
            'nested translations' => [
                'translations' => collect([
                    'auth' => [
                        'failed' => 'Błędne dane logowania',
                        'throttle' => 'Za dużo prób logowania',
                    ],
                    'simple' => 'prosta wartość',
                ]),
                'expected' => "<?php\n\nreturn [\n\t\"auth\" => [\n\t\t\"failed\" => \"Błędne dane logowania\",\n\t\t\"throttle\" => \"Za dużo prób logowania\",\n\t],\n\t\"simple\" => \"prosta wartość\",\n];",
            ],
            'deeply nested translations' => [
                'translations' => collect([
                    'level1' => [
                        'level2' => [
                            'level3' => 'deep value',
                        ],
                    ],
                ]),
                'expected' => "<?php\n\nreturn [\n\t\"level1\" => [\n\t\t\"level2\" => [\n\t\t\t\"level3\" => \"deep value\",\n\t\t],\n\t],\n];",
            ],
        ];
    }

    #[Test]
    #[DataProvider('translationsWithSpecialCharactersDataProvider')]
    public function parse_content_handles_special_characters(Collection $translations, string $expected): void
    {
        $result = $this->generator->parseContent($translations);

        $this->assertEquals($expected, $result);
    }

    public static function translationsWithSpecialCharactersDataProvider(): array
    {
        return [
            'quotes in values' => [
                'translations' => collect(['message' => 'He said "Hello"']),
                'expected' => "<?php\n\nreturn [\n\t\"message\" => \"He said \"Hello\"\",\n];",
            ],
            'special characters' => [
                'translations' => collect(['special' => 'ąęćńłśżź']),
                'expected' => "<?php\n\nreturn [\n\t\"special\" => \"ąęćńłśżź\",\n];",
            ],
            'newlines in values' => [
                'translations' => collect(['multiline' => "Line 1\nLine 2"]),
                'expected' => "<?php\n\nreturn [\n\t\"multiline\" => \"Line 1\nLine 2\",\n];",
            ],
        ];
    }

    #[Test]
    public function parse_content_with_empty_nested_array(): void
    {
        $translations = collect([
            'empty_section' => [],
            'filled_section' => ['key' => 'value'],
        ]);

        $result = $this->generator->parseContent($translations);

        $expected = "<?php\n\nreturn [\n\t\"empty_section\" => [\n\t],\n\t\"filled_section\" => [\n\t\t\"key\" => \"value\",\n\t],\n];";

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function parse_content_maintains_key_order(): void
    {
        $translations = collect([
            'z_key' => 'last',
            'a_key' => 'first',
            'middle_key' => 'middle',
        ]);

        $result = $this->generator->parseContent($translations);

        $this->assertStringContainsString('z_key', $result);
        $this->assertStringContainsString('a_key', $result);
        $this->assertStringContainsString('middle_key', $result);

        $posZ = strpos($result, 'z_key');
        $posA = strpos($result, 'a_key');
        $posMiddle = strpos($result, 'middle_key');

        $this->assertLessThan($posA, $posZ);
        $this->assertLessThan($posMiddle, $posA);
    }
}
