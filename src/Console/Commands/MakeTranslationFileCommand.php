<?php

namespace Krzar\LaravelTranslationGenerator\Console\Commands;

use Illuminate\Console\Command;
use Krzar\LaravelTranslationGenerator\Services\MakeTranslationFIleService;

class MakeTranslationFileCommand extends Command
{
    protected $signature = 'make:translation-file {name}';

    protected $description = 'Create a new translation file for every lang';

    public function __construct(
        private MakeTranslationFIleService $makeTranslationFIleService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $fileName = $this->argument('name');

        $this->makeTranslationFIleService->generate($fileName);

        $this->info("Translation file '$fileName.php' has been created for every language.");

        return self::SUCCESS;
    }
}
