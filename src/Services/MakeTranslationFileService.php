<?php

namespace Krzar\LaravelTranslationGenerator\Services;

use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Services\Finders\LanguagesFinder;
use Krzar\LaravelTranslationGenerator\Services\Generators\PhpFileGenerator;

readonly class MakeTranslationFileService
{
    public function __construct(
        private PhpFileGenerator $phpFileGenerator,
        private LanguagesFinder  $languagesFinder
    ) {}

    public function generate(string $name, Collection $languages): void
    {
        if ($languages->isEmpty()) {
            $languages = $this->languagesFinder->getAvailableLanguages();
        }

        $languages->each(
            fn (string $lang) => $this->generateFile($name, $lang)
        );
    }

    private function generateFile(string $name, string $lang): void
    {
        $path = lang_path("$lang/$name.php");

        file_put_contents($path, $this->phpFileGenerator->parseContent());
    }
}
