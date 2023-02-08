<?php

namespace Krzar\LaravelTranslationGenerator\Services\Generators;

use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Exceptions\FallbackLanguageFileNotExistsException;
use Krzar\LaravelTranslationGenerator\Services\TranslationsFixer;

abstract class TranslationGenerator
{
    protected string $lang;

    protected string $fallback;

    protected bool $overwrite;

    protected bool $clearValues;

    protected ?string $currentFileName = null;

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

    /**
     * @throws FallbackLanguageFileNotExistsException
     */
    protected function generateSingle(): void
    {
        $translations = $this->getTranslations($this->fallback);

        if($translations === null) {
            throw new FallbackLanguageFileNotExistsException(
                $this->fallback,
                $this->currentFileName ?: "$this->fallback.json"
            );
        }

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

    /**
     * @throws FallbackLanguageFileNotExistsException
     */
    public abstract function generate(): void;

    protected abstract function putToFile(Collection $translations): void;

    protected abstract function getTranslations(string $locale): ?Collection;
}
