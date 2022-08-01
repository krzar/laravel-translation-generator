<?php

namespace Krzar\LaravelTranslationGenerator\Services\Generators;

class JsonFileGenerator extends TranslationGenerator
{
    public function generate()
    {
        $translations = $this->getTranslations($this->fallback);

        if ($translations) {
            $targetPath = lang_path("$this->lang.json");
            $currentTranslations = $this->getTranslations($this->lang);

            if (!$this->overwrite && $currentTranslations) {
                $translations = $this->fixWithCurrentTranslations($translations, $currentTranslations);
            } else if ($this->clearValues) {
                $translations = $this->clearTranslationsValues($translations);
            }

            file_put_contents($targetPath, json_encode($translations, JSON_PRETTY_PRINT));
        }
    }

    protected function getTranslations(string $locale, ?string $key = null): ?array
    {
        $path = lang_path("$locale.json");

        return file_exists($path) ? json_decode(file_get_contents($path), true) : null;
    }
}
