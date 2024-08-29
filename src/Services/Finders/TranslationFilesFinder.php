<?php

namespace Krzar\LaravelTranslationGenerator\Services\Finders;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TranslationFilesFinder
{
    private const PHP_EXT = '.php';

    private const JSON_EXT = '.json';

    public static function phpFiles(string $lang, ?string $package = null): Collection
    {
        if ($package) {
            $path = lang_path("vendor/$package/$lang");
        } else {
            $path = lang_path($lang);
        }

        if (file_exists($path)) {
            return collect(scandir($path))->filter(fn (string $file) => Str::endsWith($file, self::PHP_EXT));
        }

        return collect();
    }

    public static function jsonFile(string $lang, ?string $package = null): string
    {
        if ($package) {
            return lang_path("vendor/$package/$lang".self::JSON_EXT);
        }

        return lang_path($lang.self::JSON_EXT);
    }
}
