<?php

require_once __DIR__.'/../vendor/autoload.php';

if (! function_exists('lang_path')) {
    function lang_path($path = ''): string
    {
        global $tempLangPath;

        return $tempLangPath.($path ? DIRECTORY_SEPARATOR.$path : '');
    }
}
