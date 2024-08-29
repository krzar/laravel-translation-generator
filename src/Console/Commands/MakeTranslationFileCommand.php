<?php

namespace Krzar\LaravelTranslationGenerator\Console\Commands;

use Illuminate\Console\Command;
use Krzar\LaravelTranslationGenerator\Services\MakeTranslationFileService;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;

class MakeTranslationFileCommand extends Command
{
    protected $signature = 'make:translation-file {name?}';

    protected $description = 'Create a new translation file for every lang';

    public function __construct(
        private readonly MakeTranslationFileService $makeTranslationFileService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $fileName = $this->getFileName();

        $this->makeTranslationFileService->generate($fileName);

        info("Translation file '$fileName.php' has been created for every language.");

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
}
