<?php

namespace Krzar\LaravelTranslationGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Krzar\LaravelTranslationGenerator\Services\Finders\LanguagesFinder;
use Krzar\LaravelTranslationGenerator\Services\MakeTranslationFileService;

use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\text;

class MakeTranslationFileCommand extends Command
{
    protected $signature = 'make:translation-file {name?}';

    protected $description = 'Create a new translation file for every lang';

    public function __construct(
        private readonly MakeTranslationFileService $makeTranslationFileService,
        private readonly LanguagesFinder $languagesFinder,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $fileName = $this->getFileName();
        $languages = $this->getLanguages();

        $this->makeTranslationFileService->generate($fileName, $languages);

        info("Translation file '$fileName.php' has been created for given languages.");

        return self::SUCCESS;
    }

    private function getFileName(): string
    {
        $fileName = $this->argument('name');

        if (empty($fileName)) {
            $fileName = text('Enter translation file name');
        }

        return $fileName;
    }

    private function getLanguages(): Collection
    {
        $availableLanguages = $this->languagesFinder->getAvailableLanguages();

        return collect(multiselect(
            label: 'Select languages for which you want to create translation file.',
            options: $availableLanguages,
            hint: 'If you want to select all languages, just click Enter.'
        ));
    }
}
