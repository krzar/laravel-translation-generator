# Laravel Translation Generator
![license mit](https://badgen.net/github/license/krzar/laravel-translation-generator)
![release](https://badgen.net/github/release/krzar/laravel-translation-generator/master)
![last commit](https://badgen.net/github/last-commit/krzar/laravel-translation-generator)

This package allows you to:
- Generate translation files for the specified language,
- Generate new translation files for each language,
- Completing missing keys for translations

## Supports

| Laravel Version | Package Version | Supported          |
|-----------------|-----------------|--------------------|
| 9.x             | v2.x            | :white_check_mark: |
| 8.x             | v1.x            | :x:                |

## Installation

```bash
composer require krzar/laravel-translation-generator
```

## Usage

### Generate new translation

Generate new translation files for `es` language.
```bash
php artisan make:translation es
```

If the file exists, it will be completed with the missing keys.

Files and keys will be copied based on the fallback locale specified in the app configuration.

You can change fallback locale.

```bash
php artisan make:translation es --fallback=de
```

You can also overwrite all values if file currently exists.

```bash
php artisan make:translation es --overwrite
```

All values will be copied from fallback locale by default.
If you want to clear every translation value you can use clear-values option.

```bash
php artisan make:translation es --clear-values
```

This will clear values only for new keys, to clear everything, combine two options.

```bash
php artisan make:translation es --clear-values --overwrite
```

### Generate new translation file

Generate new php translation file for every language.

```bash
php artisan make:translation-file common
```

This will generate new php file `common.php` in every language folder.
