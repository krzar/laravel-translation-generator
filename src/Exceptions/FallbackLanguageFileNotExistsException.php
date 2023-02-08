<?php

namespace Krzar\LaravelTranslationGenerator\Exceptions;

use Exception;

class FallbackLanguageFileNotExistsException extends Exception
{
    public function __construct(string $fallback, string $fileName)
    {
        parent::__construct("File '$fileName' not exists for fallback '$fallback' language.");
    }
}
