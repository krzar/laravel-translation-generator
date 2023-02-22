<?php

namespace Krzar\LaravelTranslationGenerator\Console\Commands;

use Illuminate\Console\Command;
use Krzar\LaravelTranslationGenerator\Exceptions\FallbackLanguageFileNotExistsException;
use Krzar\LaravelTranslationGenerator\Services\Generators\JsonFileGenerator;
use Krzar\LaravelTranslationGenerator\Services\Generators\PhpFileGenerator;
use Krzar\LaravelTranslationGenerator\Services\Generators\TranslationGenerator;
use Krzar\LaravelTranslationGenerator\Services\PackagesTranslationsService;

class MakeTranslationCommand extends Command
{
    protected $signature = 'make:translation {lang} {--fallback=} {--overwrite} {--clear-values}';

    protected $description = 'Create a new translation files for given lang';

    private const GENERATORS = [
        PhpFileGenerator::class,
        JsonFileGenerator::class,
    ];

    public function __construct(
        private PackagesTranslationsService $packagesTranslationsService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $lang = $this->argument('lang');
        $fallback = $this->option('fallback') ?: config('app.fallback_locale');
        $overwrite = $this->option('overwrite');
        $clearValues = $this->option('clear-values');

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
}
