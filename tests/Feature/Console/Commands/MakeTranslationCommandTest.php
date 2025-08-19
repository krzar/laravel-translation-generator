<?php

namespace Krzar\LaravelTranslationGenerator\Tests\Feature\Console\Commands;

use Illuminate\Console\Command;
use Krzar\LaravelTranslationGenerator\Console\Commands\MakeTranslationCommand;
use Krzar\LaravelTranslationGenerator\Services\Generators\JsonFileGenerator;
use Krzar\LaravelTranslationGenerator\Services\Generators\PhpFileGenerator;
use Krzar\LaravelTranslationGenerator\Services\PackagesTranslationsService;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MakeTranslationCommandTest extends TestCase
{
    private MakeTranslationCommand $command;

    private PackagesTranslationsService $packagesTranslationsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->packagesTranslationsService = Mockery::mock(PackagesTranslationsService::class);
        $this->command = new MakeTranslationCommand($this->packagesTranslationsService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function command_has_correct_signature(): void
    {
        $reflection = new \ReflectionClass($this->command);
        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);

        $this->assertEquals(
            'make:translation {lang?} {--fallback=} {--overwrite} {--clear-values}',
            $signatureProperty->getValue($this->command)
        );
    }

    #[Test]
    public function command_has_correct_description(): void
    {
        $this->assertEquals(
            'Create a new translation files for given lang',
            $this->command->getDescription()
        );
    }

    #[Test]
    #[DataProvider('commandOptionsDataProvider')]
    public function command_has_correct_options(string $optionName, bool $shouldExist): void
    {
        $definition = $this->command->getDefinition();

        if ($shouldExist) {
            $this->assertTrue($definition->hasOption($optionName));
        } else {
            $this->assertFalse($definition->hasOption($optionName));
        }
    }

    public static function commandOptionsDataProvider(): array
    {
        return [
            'has fallback option' => ['fallback', true],
            'has overwrite option' => ['overwrite', true],
            'has clear-values option' => ['clear-values', true],
            'does not have invalid option' => ['invalid-option', false],
        ];
    }

    #[Test]
    #[DataProvider('commandArgumentsDataProvider')]
    public function command_has_correct_arguments(string $argumentName, bool $shouldExist): void
    {
        $definition = $this->command->getDefinition();

        if ($shouldExist) {
            $this->assertTrue($definition->hasArgument($argumentName));
        } else {
            $this->assertFalse($definition->hasArgument($argumentName));
        }
    }

    public static function commandArgumentsDataProvider(): array
    {
        return [
            'has lang argument' => ['lang', true],
            'does not have name argument' => ['name', false],
            'does not have invalid argument' => ['invalid-argument', false],
        ];
    }

    #[Test]
    public function command_uses_correct_generators(): void
    {
        $reflection = new \ReflectionClass($this->command);
        $generatorsConstant = $reflection->getConstant('GENERATORS');

        $this->assertContains(PhpFileGenerator::class, $generatorsConstant);
        $this->assertContains(JsonFileGenerator::class, $generatorsConstant);
        $this->assertCount(2, $generatorsConstant);
    }

    #[Test]
    public function command_injects_packages_translations_service(): void
    {
        $reflection = new \ReflectionClass($this->command);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('packagesTranslationsService', $parameters[0]->getName());
        $this->assertEquals(PackagesTranslationsService::class, $parameters[0]->getType()->getName());
    }

    #[Test]
    public function command_extends_illuminate_command(): void
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    #[Test]
    public function command_has_handle_method(): void
    {
        $this->assertTrue(method_exists($this->command, 'handle'));

        $reflection = new \ReflectionMethod($this->command, 'handle');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());
    }

    #[Test]
    #[DataProvider('privateMethodsDataProvider')]
    public function command_has_private_methods(string $methodName): void
    {
        $reflection = new \ReflectionClass($this->command);

        $this->assertTrue($reflection->hasMethod($methodName));

        $method = $reflection->getMethod($methodName);
        $this->assertTrue($method->isPrivate());
    }

    public static function privateMethodsDataProvider(): array
    {
        return [
            ['generatePackagesTranslations'],
            ['getLang'],
            ['getFallback'],
            ['getOverwrite'],
            ['getClearValues'],
        ];

}
