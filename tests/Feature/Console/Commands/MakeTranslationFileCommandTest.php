<?php

namespace Krzar\LaravelTranslationGenerator\Tests\Feature\Console\Commands;

use Illuminate\Console\Command;
use Krzar\LaravelTranslationGenerator\Console\Commands\MakeTranslationFileCommand;
use Krzar\LaravelTranslationGenerator\Services\Finders\LanguagesFinder;
use Krzar\LaravelTranslationGenerator\Services\MakeTranslationFileService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MakeTranslationFileCommandTest extends TestCase
{
    private MakeTranslationFileCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $makeTranslationFileService = $this->createStub(MakeTranslationFileService::class);
        $languagesFinder = $this->createStub(LanguagesFinder::class);

        $this->command = new MakeTranslationFileCommand(
            $makeTranslationFileService,
            $languagesFinder
        );
    }

    #[Test]
    public function commandHasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass($this->command);
        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);

        $this->assertEquals(
            'make:translation-file {name?}',
            $signatureProperty->getValue($this->command)
        );
    }

    #[Test]
    public function commandHasCorrectDescription(): void
    {
        $this->assertEquals(
            'Create a new translation file for every lang',
            $this->command->getDescription()
        );
    }

    #[Test]
    #[DataProvider('commandArgumentsDataProvider')]
    public function commandHasCorrectArguments(string $argumentName, bool $shouldExist): void
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
            'has name argument' => ['name', true],
            'does not have lang argument' => ['lang', false],
            'does not have invalid argument' => ['invalid-argument', false],
        ];
    }

    #[Test]
    public function commandInjectsCorrectServices(): void
    {
        $reflection = new \ReflectionClass($this->command);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(2, $parameters);

        $this->assertEquals('makeTranslationFileService', $parameters[0]->getName());
        $this->assertEquals(MakeTranslationFileService::class, $parameters[0]->getType()->getName());

        $this->assertEquals('languagesFinder', $parameters[1]->getName());
        $this->assertEquals(LanguagesFinder::class, $parameters[1]->getType()->getName());
    }

    #[Test]
    public function commandExtendsIlluminateCommand(): void
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    #[Test]
    public function commandHasHandleMethod(): void
    {
        $this->assertTrue(method_exists($this->command, 'handle'));

        $reflection = new \ReflectionMethod($this->command, 'handle');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());
    }

    #[Test]
    #[DataProvider('privateMethodsDataProvider')]
    public function commandHasPrivateMethods(string $methodName): void
    {
        $reflection = new \ReflectionClass($this->command);

        $this->assertTrue($reflection->hasMethod($methodName));

        $method = $reflection->getMethod($methodName);
        $this->assertTrue($method->isPrivate());
    }

    public static function privateMethodsDataProvider(): array
    {
        return [
            ['getFileName'],
            ['getLanguages'],
        ];
    }

    #[Test]
    public function commandHasNoOptions(): void
    {
        $definition = $this->command->getDefinition();
        $options = $definition->getOptions();

        $this->assertEmpty(array_filter($options, function ($option) {
            return ! in_array($option->getName(), ['help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction']);
        }));
    }

    #[Test]
    public function commandNameArgumentIsOptional(): void
    {
        $definition = $this->command->getDefinition();
        $nameArgument = $definition->getArgument('name');

        $this->assertFalse($nameArgument->isRequired());
    }
}
