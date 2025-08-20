<?php

namespace Krzar\LaravelTranslationGenerator;

use Illuminate\Support\ServiceProvider;
use Krzar\LaravelTranslationGenerator\Console\Commands\MakeTranslationCommand;
use Krzar\LaravelTranslationGenerator\Console\Commands\MakeTranslationFileCommand;

class LaravelTranslationGeneratorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeTranslationCommand::class,
                MakeTranslationFileCommand::class,
            ]);
        }
    }
}
