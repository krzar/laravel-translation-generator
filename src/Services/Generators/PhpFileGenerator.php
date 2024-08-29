<?php

namespace Krzar\LaravelTranslationGenerator\Services\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Krzar\LaravelTranslationGenerator\Exceptions\FallbackLanguageFileNotExistsException;
use Krzar\LaravelTranslationGenerator\Services\Finders\TranslationFilesFinder;

class PhpFileGenerator extends TranslationGenerator
{
    private string $targetPath;

    public function generate(): void
    {
        $this->generateFiles();

        if ($this->generatePackagesTranslations) {
            $this->generatePackagesFiles();
        }
    }

    public function parseContent(?Collection $translations = null): string
    {
        return sprintf(
            '<?php%sreturn [%s%s];',
            PHP_EOL.PHP_EOL,
            PHP_EOL,
            $translations ? $this->translationsToString($translations) : ''
        );
    }

    /**
     * @throws FallbackLanguageFileNotExistsException
     */
    private function generatePackagesFiles(): void
    {
        $this->packagesTranslationsService->findPackages()->each(function (string $package) {
            $this->currentPackage = $package;
            $this->generateFiles();
        });
    }

    /**
     * @throws FallbackLanguageFileNotExistsException
     */
    private function generateFiles(): void
    {
        $this->setTargetPath();

        TranslationFilesFinder::phpFiles($this->fallback, $this->currentPackage)->each(function (string $fileName) {
            $this->currentFileName = $fileName;

            $this->generateSingle();
        });
    }

    private function setTargetPath(): void
    {
        $this->targetPath = lang_path(
            $this->currentPackage ? "vendor/$this->currentPackage/$this->lang" : $this->lang
        );

        if (! file_exists($this->targetPath)) {
            mkdir($this->targetPath);
        }
    }

    private function translationsToString(Collection $translations, int $level = 1): string
    {
        $tabs = sprintf("%'\t{$level}s", '');

        return $translations->reduce(function (string $string, mixed $value, string $key) use ($level, $tabs) {
            if (is_string($value)) {
                $toAppend = sprintf("$tabs\"%s\" => \"%s\",\n", $key, $value);
            } else {
                $toAppend = sprintf(
                    "$tabs\"%s\" => [\n%s$tabs],\n",
                    $key,
                    $this->translationsToString(collect($value), $level + 1)
                );
            }

            return "{$string}{$toAppend}";
        }, '');
    }

    protected function getTranslations(string $locale): ?Collection
    {
        $path = $this->currentPackage ? "$this->currentPackage::$this->currentFileName" : $this->currentFileName;

        $translations = Lang::get(str_replace('.php', '', $path), [], $locale);

        return $translations !== '' ? collect($translations) : null;
    }

    protected function putToFile(Collection $translations): void
    {
        $targetPath = "$this->targetPath/$this->currentFileName";
        $content = $this->parseContent($translations);

        file_put_contents($targetPath, $content);
    }
}
