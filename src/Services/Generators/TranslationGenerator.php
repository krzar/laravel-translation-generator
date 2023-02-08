<?php

namespace Krzar\LaravelTranslationGenerator\Services\Generators;

use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Services\TranslationsFixer;

abstract class TranslationGenerator
{
    protected string $lang;

    protected string $fallback;

    protected bool $overwrite;

    protected bool $clearValues;

    public function setup(
        string $lang,
        string $fallback,
        bool $overwrite,
        bool $clearValues
    ): TranslationGenerator
    {
        $this->lang = $lang;
        $this->fallback = $fallback;
        $this->overwrite = $overwrite;
        $this->clearValues = $clearValues;

        return $this;
    }

    protected function generateSingle(): void
    {
        $translations = $this->getTranslations($this->fallback);
        $currentTranslations = $this->getTranslations($this->lang);

        if (!$this->overwrite && $currentTranslations) {
            $translations = TranslationsFixer::fixToOtherTranslations(
                $translations,
                $currentTranslations,
                $this->clearValues
            );
        } else if ($this->clearValues) {
            $translations = TranslationsFixer::fixToEmpty($translations);
        }

        $this->putToFile($translations);
    }

    public abstract function generate(): void;

    protected abstract function putToFile(Collection $translations): void;

    protected abstract function getTranslations(string $locale): ?Collection;
}
