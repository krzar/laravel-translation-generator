<?php

namespace Krzar\LaravelTranslationGenerator\Services\Generators;

use Illuminate\Support\Facades\Lang;

abstract class TranslationGenerator
{
    /** @var string */
    protected $lang;

    /** @var string */
    protected $fallback;

    /** @var bool */
    protected $overwrite;

    /** @var bool */
    protected $clearValues;

    public function setup(string $lang, string $fallback, bool $overwrite, bool $clearValues): TranslationGenerator
    {
        $this->lang = $lang;
        $this->fallback = $fallback;
        $this->overwrite = $overwrite;
        $this->clearValues = $clearValues;

        return $this;
    }

    public abstract function generate();

    protected function clearTranslationsValues(array $translations): array
    {
        foreach ($translations as $key => $value) {
            if (is_array($value)) {
                $clearedTranslations[$key] = $this->clearTranslationsValues($value);
            } else {
                $clearedTranslations[$key] = '';
            }
        }

        return $clearedTranslations ?? [];
    }

    protected function fixWithCurrentTranslations(array $translations, array $currentTranslations): array
    {
        foreach ($translations as $key => $value) {
            if (is_array($value)) {
                $fixedTranslation[$key] = $this->fixWithCurrentTranslations(
                    $value,
                    $currentTranslations[$key]
                );
            } else {
                $fixedTranslation[$key] = $currentTranslations[$key] ?? $value;
            }
        }

        return $fixedTranslation ?? [];
    }

    protected abstract function getTranslations(string $locale, ?string $key = null): ?array;
}
