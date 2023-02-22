<?php

namespace Krzar\LaravelTranslationGenerator\Services\Generators;

use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Exceptions\FallbackLanguageFileNotExistsException;
use Krzar\LaravelTranslationGenerator\Services\PackagesTranslationsService;
use Krzar\LaravelTranslationGenerator\Services\TranslationsFixer;

abstract class TranslationGenerator
{
    protected ?string $currentFileName = null;

    protected ?string $currentPackage = null;

    protected PackagesTranslationsService $packagesTranslationsService;

    public function __construct(
        protected string $lang = '',
        protected string $fallback = '',
        protected bool $overwrite = false,
        protected bool $clearValues = false,
        protected bool $generatePackagesTranslations = false
    ) {
        $this->packagesTranslationsService = new PackagesTranslationsService();
    }

    /**
     * @throws FallbackLanguageFileNotExistsException
     */
    protected function generateSingle(): void
    {
        $translations = $this->getTranslations($this->fallback);

        if ($translations === null) {
            throw new FallbackLanguageFileNotExistsException(
                $this->fallback,
                $this->currentFileName ?: "$this->fallback.json"
            );
        }

        $currentTranslations = $this->getTranslations($this->lang);

        if (! $this->overwrite && $currentTranslations) {
            $translations = TranslationsFixer::fixToOtherTranslations(
                $translations,
                $currentTranslations,
                $this->clearValues
            );
        } elseif ($this->clearValues) {
            $translations = TranslationsFixer::fixToEmpty($translations);
        }

        $this->putToFile($translations);
    }

    /**
     * @throws FallbackLanguageFileNotExistsException
     */
    abstract public function generate(): void;

    abstract protected function putToFile(Collection $translations): void;

    abstract protected function getTranslations(string $locale): ?Collection;
}
