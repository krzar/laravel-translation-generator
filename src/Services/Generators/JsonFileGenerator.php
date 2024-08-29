<?php

namespace Krzar\LaravelTranslationGenerator\Services\Generators;

use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Exceptions\FallbackLanguageFileNotExistsException;
use Krzar\LaravelTranslationGenerator\Services\Finders\TranslationFilesFinder;

class JsonFileGenerator extends TranslationGenerator
{
    public function generate(): void
    {
        if ($this->filesNotExists()) {
            return;
        }

        $this->generateSingle();

        if ($this->generatePackagesTranslations) {
            $this->generatePackagesFiles();
        }
    }

    protected function getTranslations(string $locale): ?Collection
    {
        $path = TranslationFilesFinder::jsonFile($locale, $this->currentPackage);

        return file_exists($path) ? collect(json_decode(file_get_contents($path), true)) : null;
    }

    protected function putToFile(Collection $translations): void
    {
        $targetPath = TranslationFilesFinder::jsonFile($this->lang, $this->currentPackage);
        $content = json_encode($translations, JSON_PRETTY_PRINT);

        file_put_contents($targetPath, $content);
    }

    /**
     * @throws FallbackLanguageFileNotExistsException
     */
    private function generatePackagesFiles(): void
    {
        $this->packagesTranslationsService->findPackages()->each(function (string $package) {
            $this->currentPackage = $package;
            $this->generateSingle();
        });
    }

    private function filesNotExists(): bool
    {
        return TranslationFilesFinder::jsonFiles($this->currentPackage)->isEmpty();
    }
}
