<?php

namespace Krzar\LaravelTranslationGenerator\Services\Generators;

use Illuminate\Support\Collection;

class JsonFileGenerator extends TranslationGenerator
{
    public function generate(): void
    {
        $this->generateSingle();
    }

    protected function getTranslations(string $locale): ?Collection
    {
        $path = lang_path("$locale.json");

        return file_exists($path) ? collect(json_decode(file_get_contents($path), true)) : null;
    }

    protected function putToFile(Collection $translations): void {
        $targetPath = lang_path("$this->lang.json");
        $content = json_encode($translations, JSON_PRETTY_PRINT);

        file_put_contents($targetPath, $content);
    }
}
