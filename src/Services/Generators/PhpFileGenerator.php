<?php

namespace Krzar\LaravelTranslationGenerator\Services\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class PhpFileGenerator extends TranslationGenerator
{
    private string $targetPath;

    public function generate(): void
    {
        $this->targetPath = lang_path($this->lang);

        if (! file_exists($this->targetPath)) {
            mkdir($this->targetPath);
        }

        $this->getTranslationsFiles()->each(
            function (string $fileName) {
                $this->currentFileName = $fileName;

                $this->generateSingle();
            }
        );
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

    private function getTranslationsFiles(): Collection
    {
        $path = lang_path($this->fallback);

        if (file_exists($path)) {
            return collect(scandir($path))->filter(
                fn (string $fileName) => $fileName !== '.' && $fileName !== '..'
            );
        }

        return collect();
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
        $translations = Lang::get(str_replace('.php', '', $this->currentFileName), [], $locale);

        return $translations !== '' ? collect($translations) : null;
    }

    protected function putToFile(Collection $translations): void
    {
        $targetPath = "$this->targetPath/$this->currentFileName";
        $content = $this->parseContent($translations);

        file_put_contents($targetPath, $content);
    }
}
