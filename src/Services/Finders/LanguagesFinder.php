<?php

namespace Krzar\LaravelTranslationGenerator\Services\Finders;

use Illuminate\Support\Collection;

class LanguagesFinder
{
    private const IGNORED_DIRECTORIES = ['.', '..'];

    public function getAvailableLanguages(): Collection
    {
        return collect(scandir(lang_path()))->filter(
            fn (string $directory) => $this->filterDirectory($directory)
        )->mapWithKeys(
            fn (string $directory) => [$directory => $directory]
        );
    }

    private function filterDirectory(string $directory): bool
    {
        return ! in_array($directory, self::IGNORED_DIRECTORIES) && is_dir(lang_path($directory));
    }
}
