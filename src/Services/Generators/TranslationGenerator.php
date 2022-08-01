<?php

namespace Krzar\LaravelTranslationGenerator\Services\Generators;

use Illuminate\Support\Collection;

abstract class TranslationGenerator
{
    protected string $lang;

    protected string $fallback;

    protected bool $overwrite;

    protected bool $clearValues;

    public function setup(string $lang, string $fallback, bool $overwrite, bool $clearValues): TranslationGenerator
    {
        $this->lang = $lang;
        $this->fallback = $fallback;
        $this->overwrite = $overwrite;
        $this->clearValues = $clearValues;

        return $this;
    }

    public abstract function generate();

    protected function clearTranslationsValues(Collection $translations): Collection
    {
        return $translations->map(function (string|array $value) {
            if (is_string($value)) {
                return '';
            }

            return $this->clearTranslationsValues(collect($value));
        });
    }

    protected function fixWithCurrentTranslations(
        Collection $translations,
        Collection $currentTranslations
    ): Collection
    {
        return $translations->map(function (string|array $value, string $key) use ($currentTranslations) {
            if (is_string($value)) {
                return $currentTranslations->get($key) ?: ($this->clearValues ? '' : $value);
            }

            return $this->fixWithCurrentTranslations(
                collect($value),
                collect($currentTranslations->get($key))
            );
        });
    }

    protected abstract function getTranslations(string $locale, ?string $key = null): ?Collection;
}
