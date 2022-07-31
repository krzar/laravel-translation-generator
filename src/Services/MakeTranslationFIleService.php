<?php

namespace Krzar\LaravelTranslationGenerator\Services;

use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Services\Generators\PhpFileGenerator;

class MakeTranslationFIleService
{
    /**
     * @var PhpFileGenerator
     */
    private $phpFileGenerator;

    public function __construct(PhpFileGenerator $phpFileGenerator)
    {
        $this->phpFileGenerator = $phpFileGenerator;
    }

    public function generate(string $name)
    {
        $this->getLanguages()->each(function (string $lang) use ($name) {
            $this->generateFile($name, $lang);
        });
    }

    private function getLanguages(): Collection
    {
        return collect(scandir(lang_path()))->filter(function (string $fileName) {
            return $fileName !== '.' && $fileName !== '..' && is_dir(lang_path($fileName));
        });
    }

    private function generateFile(string $name, string $lang)
    {
        $path = lang_path("$lang/$name.php");

        file_put_contents($path, $this->phpFileGenerator->fileContent());
    }
}
