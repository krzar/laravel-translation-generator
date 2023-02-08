<?php

namespace Krzar\LaravelTranslationGenerator\Console\Commands;

use Illuminate\Console\Command;
use Krzar\LaravelTranslationGenerator\Exceptions\FallbackLanguageFileNotExistsException;
use Krzar\LaravelTranslationGenerator\Services\Generators\JsonFileGenerator;
use Krzar\LaravelTranslationGenerator\Services\Generators\PhpFileGenerator;
use Krzar\LaravelTranslationGenerator\Services\Generators\TranslationGenerator;

class MakeTranslationCommand extends Command
{
    protected $signature = 'make:translation {lang} {--fallback=} {--overwrite} {--clear-values}';

    protected $description = 'Create a new translation files for given lang';

    private const GENERATORS = [
        PhpFileGenerator::class,
        JsonFileGenerator::class
    ];

    public function handle(): int
    {
        $lang = $this->argument('lang');
        $fallback = $this->option('fallback') ?: config('app.fallback_locale');
        $overwrite = $this->option('overwrite');
        $clearValues = $this->option('clear-values');

        foreach (self::GENERATORS as $generatorClass) {
            /** @var TranslationGenerator $generator */
            $generator = new $generatorClass();

            try {
                $generator->setup(
                    $lang,
                    $fallback,
                    $overwrite,
                    $clearValues
                )->generate();
            } catch (FallbackLanguageFileNotExistsException $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }
        }

        $this->info("Translations for '$lang' language has been created.");

        return self::SUCCESS;
    }
}
