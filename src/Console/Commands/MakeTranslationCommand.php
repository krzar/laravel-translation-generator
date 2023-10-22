<?php

namespace Krzar\LaravelTranslationGenerator\Console\Commands;

use Illuminate\Console\Command;
use Krzar\LaravelTranslationGenerator\Exceptions\FallbackLanguageFileNotExistsException;
use Krzar\LaravelTranslationGenerator\Services\Generators\JsonFileGenerator;
use Krzar\LaravelTranslationGenerator\Services\Generators\PhpFileGenerator;
use Krzar\LaravelTranslationGenerator\Services\Generators\TranslationGenerator;
use Krzar\LaravelTranslationGenerator\Services\PackagesTranslationsService;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class MakeTranslationCommand extends Command
{
    protected $signature = 'make:translation {lang?} {--fallback=} {--overwrite} {--clear-values}';

    protected $description = 'Create a new translation files for given lang';

    private const GENERATORS = [
        PhpFileGenerator::class,
        JsonFileGenerator::class,
    ];

    public function __construct(
        private readonly PackagesTranslationsService $packagesTranslationsService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $lang = $this->getLang();
        $fallback = $this->getFallback();
        $overwrite = $this->getOverwrite();
        $clearValues = $this->getClearValues();

        $generatePackagesTranslations = $this->generatePackagesTranslations();

        foreach (self::GENERATORS as $generatorClass) {
            /** @var TranslationGenerator $generator */
            $generator = new $generatorClass(
                $lang,
                $fallback,
                $overwrite,
                $clearValues,
                $generatePackagesTranslations
            );

            try {
                $generator->generate();
            } catch (FallbackLanguageFileNotExistsException $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }
        }

        $this->info("Translations for '$lang' language has been created.");

        return self::SUCCESS;
    }

    private function generatePackagesTranslations(): bool
    {
        $packages = $this->packagesTranslationsService->findPackages();

        if ($packages) {
            $this->info('Translation files were found for the following packages:');

            $packages->each(function (string $package) {
                $this->line("- $package");
            });

            return $this->confirm('Do you want to generate files for packages as well?');
        }

        return false;
    }

    private function getLang(): string
    {
        $lang = $this->argument('lang');

        if (! $lang) {
            $lang = text(
                label: 'Enter language code',
                placeholder: 'Example: en, pl',
                required: true
            );
        }

        return $lang;
    }

    private function getFallback(): string
    {
        $fallback = $this->option('fallback');

        if (! $fallback) {
            $fallback = text(
                label: 'Enter fallback language code',
                placeholder: 'Example: en, pl',
                default: config('app.fallback_locale')
            );
        }

        return $fallback;
    }

    private function getOverwrite(): bool
    {
        $overwrite = $this->option('overwrite');

        if ($overwrite === false) {
            $overwrite = confirm(
                label: 'Do you want to overwrite existing translations?',
                default: false,
                yes: 'Yes, overwrite',
                no: 'No, skip'
            );
        }

        return $overwrite;
    }

    private function getClearValues(): bool
    {
        $clearValues = $this->option('clear-values');

        if ($clearValues === false) {
            $clearValues = confirm(
                label: 'Do you want to clear existing translations?',
                default: false,
                yes: 'Yes, clear',
                no: 'No, skip'
            );
        }

        return $clearValues;
    }
}
