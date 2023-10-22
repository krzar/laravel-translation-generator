<?php

namespace Krzar\LaravelTranslationGenerator\Services;

use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Services\Generators\PhpFileGenerator;

class MakeTranslationFileService
{
    public function __construct(
        private readonly PhpFileGenerator $phpFileGenerator
    ) {
    }

    public function generate(string $name): void
    {
        $this->getLanguages()->each(
            fn (string $lang) => $this->generateFile($name, $lang)
        );
    }

    private function getLanguages(): Collection
    {
        return collect(scandir(lang_path()))->filter(
            fn (string $fileName) => $fileName !== '.' && $fileName !== '..' && is_dir(lang_path($fileName))
        );
    }

    private function generateFile(string $name, string $lang): void
    {
        $path = lang_path("$lang/$name.php");

        file_put_contents($path, $this->phpFileGenerator->parseContent());
    }
}
