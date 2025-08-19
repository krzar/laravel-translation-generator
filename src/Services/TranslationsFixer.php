<?php

namespace Krzar\LaravelTranslationGenerator\Services;

use Illuminate\Support\Collection;

class TranslationsFixer
{
    public static function fixToEmpty(Collection $translations): Collection
    {
        return $translations->map(
            fn (string|array $value) => is_string($value) ? '' : self::fixToEmpty(collect($value))
        );
    }

    public static function fixToOtherTranslations(
        Collection $translations,
        Collection $otherTranslations,
        bool $clearIfNotExists = false
    ): Collection {
        return $translations->map(fn (string|array $value, string $key) => self::fixToOtherTranslationSingle(
            $value,
            $otherTranslations->get($key),
            $clearIfNotExists
        ));
    }

    public static function fixToOtherTranslationSingle(
        string|array $translation,
        string|array|null $otherTranslation,
        bool $clearIfNotExists = false
    ): string|Collection {
        if (is_string($translation)) {
            if ($otherTranslation !== null) {
                return is_array($otherTranslation) ? collect($otherTranslation) : $otherTranslation;
            }

            return $clearIfNotExists ? '' : $translation;
        }

        return self::fixToOtherTranslations(
            collect($translation),
            collect($translation)
        );
    }
}
