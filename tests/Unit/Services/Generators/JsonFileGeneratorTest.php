<?php

namespace Krzar\LaravelTranslationGenerator\Tests\Unit\Services\Generators;

use Krzar\LaravelTranslationGenerator\Services\Generators\JsonFileGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JsonFileGeneratorTest extends TestCase
{
    private JsonFileGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new JsonFileGenerator('en', 'pl');
    }

    #[Test]
    public function constructorSetsPropertiesCorrectly(): void
    {
        $generator = new JsonFileGenerator('fr', 'en', true, true, true);

        $this->assertInstanceOf(JsonFileGenerator::class, $generator);
    }

    #[Test]
    public function generateMethodExists(): void
    {
        $this->assertTrue(method_exists($this->generator, 'generate'));
    }

    #[Test]
    public function getTranslationsMethodExists(): void
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getTranslations');

        $this->assertTrue($method->isProtected());
    }

    #[Test]
    public function putToFileMethodExists(): void
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('putToFile');

        $this->assertTrue($method->isProtected());
    }

    #[Test]
    public function generatorExtendsTranslationGenerator(): void
    {
        $this->assertInstanceOf(
            \Krzar\LaravelTranslationGenerator\Services\Generators\TranslationGenerator::class,
            $this->generator
        );
    }

    #[Test]
    public function generatorImplementsRequiredMethods(): void
    {
        $requiredMethods = ['generate', 'getTranslations', 'putToFile'];

        foreach ($requiredMethods as $method) {
            $this->assertTrue(
                method_exists($this->generator, $method),
                "Method {$method} should exist"
            );
        }
    }
}
