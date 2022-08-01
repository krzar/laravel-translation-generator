<?php

namespace Krzar\LaravelTranslationGenerator\Services\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class PhpFileGenerator extends TranslationGenerator
{
    private string $targetPath;

    public function generate()
    {
        $this->targetPath = lang_path($this->lang);

        if (!file_exists($this->targetPath)) {
            mkdir($this->targetPath);
        }

        $this->getTranslationsFiles()->filter(
            fn(string $fileName) => $fileName !== '.' && $fileName !== '..'
        )->each(
            fn(string $fileName) => $this->generateSingle($fileName)
        );
    }

    public function fileContent(array $translations = []): string
    {
        return sprintf(
            '<?php%sreturn [%s%s];',
            PHP_EOL . PHP_EOL,
            PHP_EOL,
            $this->translationsToString($translations)
        );
    }

    private function getTranslationsFiles(): Collection
    {
        return collect(scandir(lang_path($this->fallback)));
    }

    private function generateSingle(string $fileName)
    {
        $translations = $this->getTranslations($this->fallback, $fileName);
        $currentTranslations = $this->getTranslations($this->lang, $fileName);

        if (!$this->overwrite && $currentTranslations) {
            $translations = $this->fixWithCurrentTranslations($translations, $currentTranslations);
        } else if ($this->clearValues) {
            $translations = $this->clearTranslationsValues($translations);
        }

        file_put_contents($this->targetPath . '/' . $fileName, $this->fileContent($translations));
    }

    private function translationsToString(array $translations, int $level = 1): string
    {
        $tabs = sprintf("%'\t{$level}s", '');
        $string = '';

        foreach ($translations as $key => $value) {
            if (is_array($value)) {
                $string .= sprintf(
                    "$tabs\"%s\" => [\n%s$tabs],\n",
                    $key,
                    $this->translationsToString($value, $level + 1)
                );
            } else {
                $string .= sprintf("$tabs\"%s\" => \"%s\",\n", $key, $value);
            }
        }

        return $string;
    }

    protected function getTranslations(string $locale, ?string $key = null): ?array
    {
        $translations = Lang::get(str_replace('.php', '', $key), [], $locale);

        return $translations !== '' ? $translations : null;
    }
}
