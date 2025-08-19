<?php

namespace Krzar\LaravelTranslationGenerator\Tests\Unit\Services;

use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Services\Finders\LanguagesFinder;
use Krzar\LaravelTranslationGenerator\Services\Generators\PhpFileGenerator;
use Krzar\LaravelTranslationGenerator\Services\MakeTranslationFileService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MakeTranslationFileServiceTest extends TestCase
{
    #[Test]
    public function serviceIsReadonly(): void
    {
        $reflection = new \ReflectionClass(MakeTranslationFileService::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    #[Test]
    public function constructorInjectsCorrectDependencies(): void
    {
        $reflection = new \ReflectionClass(MakeTranslationFileService::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(2, $parameters);

        $this->assertEquals('phpFileGenerator', $parameters[0]->getName());
        $this->assertEquals(PhpFileGenerator::class, $parameters[0]->getType()->getName());
        $this->assertTrue($parameters[0]->isPromoted());

        $this->assertEquals('languagesFinder', $parameters[1]->getName());
        $this->assertEquals(LanguagesFinder::class, $parameters[1]->getType()->getName());
        $this->assertTrue($parameters[1]->isPromoted());
    }

    #[Test]
    public function generateMethodExists(): void
    {
        $this->assertTrue(method_exists(MakeTranslationFileService::class, 'generate'));

        $reflection = new \ReflectionMethod(MakeTranslationFileService::class, 'generate');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }

    #[Test]
    #[DataProvider('generateParametersDataProvider')]
    public function generateMethodHasCorrectParameters(int $parameterIndex, string $expectedName, string $expectedType): void
    {
        $reflection = new \ReflectionMethod(MakeTranslationFileService::class, 'generate');
        $parameters = $reflection->getParameters();

        $this->assertArrayHasKey($parameterIndex, $parameters);

        $parameter = $parameters[$parameterIndex];
        $this->assertEquals($expectedName, $parameter->getName());
        $this->assertEquals($expectedType, $parameter->getType()->getName());
    }

    public static function generateParametersDataProvider(): array
    {
        return [
            [0, 'name', 'string'],
            [1, 'languages', Collection::class],
        ];
    }

    #[Test]
    public function serviceHasPrivateGenerateFileMethod(): void
    {
        $reflection = new \ReflectionClass(MakeTranslationFileService::class);

        $this->assertTrue($reflection->hasMethod('generateFile'));

        $method = $reflection->getMethod('generateFile');
        $this->assertTrue($method->isPrivate());
        $this->assertEquals('void', $method->getReturnType()->getName());

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('name', $parameters[0]->getName());
        $this->assertEquals('lang', $parameters[1]->getName());
    }
}
