<?php

namespace Krzar\LaravelTranslationGenerator\Tests\Unit\Services\Finders;

use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Services\Finders\LanguagesFinder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LanguagesFinderTest extends TestCase
{
    #[Test]
    public function getAvailableLanguagesMethodExists(): void
    {
        $this->assertTrue(method_exists(LanguagesFinder::class, 'getAvailableLanguages'));

        $reflection = new \ReflectionMethod(LanguagesFinder::class, 'getAvailableLanguages');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(Collection::class, $reflection->getReturnType()->getName());
    }

    #[Test]
    public function finderHasIgnoredDirectoriesConstant(): void
    {
        $reflection = new \ReflectionClass(LanguagesFinder::class);

        $this->assertTrue($reflection->hasConstant('IGNORED_DIRECTORIES'));

        $constant = $reflection->getConstant('IGNORED_DIRECTORIES');
        $this->assertIsArray($constant);
        $this->assertContains('.', $constant);
        $this->assertContains('..', $constant);
    }

    #[Test]
    #[DataProvider('ignoredDirectoriesDataProvider')]
    public function ignoredDirectoriesConstantContainsCorrectValues(string $directory): void
    {
        $reflection = new \ReflectionClass(LanguagesFinder::class);
        $constant = $reflection->getConstant('IGNORED_DIRECTORIES');

        $this->assertContains($directory, $constant);
    }

    public static function ignoredDirectoriesDataProvider(): array
    {
        return [
            'current directory' => ['.'],
            'parent directory' => ['..'],
        ];
    }

    #[Test]
    public function finderHasPrivateFilterDirectoryMethod(): void
    {
        $reflection = new \ReflectionClass(LanguagesFinder::class);

        $this->assertTrue($reflection->hasMethod('filterDirectory'));

        $method = $reflection->getMethod('filterDirectory');
        $this->assertTrue($method->isPrivate());
        $this->assertEquals('bool', $method->getReturnType()->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('directory', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
    }

    #[Test]
    public function finderCanBeInstantiated(): void
    {
        $finder = new LanguagesFinder;

        $this->assertInstanceOf(LanguagesFinder::class, $finder);
    }

    #[Test]
    public function finderUsesCorrectMethodVisibilities(): void
    {
        $reflection = new \ReflectionClass(LanguagesFinder::class);

        $publicMethod = $reflection->getMethod('getAvailableLanguages');
        $this->assertTrue($publicMethod->isPublic());

        $privateMethod = $reflection->getMethod('filterDirectory');
        $this->assertTrue($privateMethod->isPrivate());
    }
}
