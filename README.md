# Laravel Translation Generator
![GitHub License](https://img.shields.io/github/license/krzar/laravel-translation-generator)
![GitHub Release](https://img.shields.io/github/v/release/krzar/laravel-translation-generator?cacheSeconds=300)
![GitHub last commit](https://img.shields.io/github/last-commit/krzar/laravel-translation-generator?cacheSeconds=300)
![GitHub branch check runs](https://img.shields.io/github/check-runs/krzar/laravel-translation-generator/master?cacheSeconds=300)

This package allows you to:
- Generate translation files for the specified language,
- Generate new translation files for each language,
- Completing missing keys for translations

## Requirements

|   Laravel   | PHP  | Package |     Supported      |
|:-----------:|:----:|:-------:|:------------------:|
|  From 11.x  | 8.2+ |   4.x   | :white_check_mark: |
|  From 10.x  | 8.1+ |   3.x   | :white_check_mark: |
| 6.x to 10.x | 8.0+ |   2.x   |        :x:         |

## Installation

```bash
composer require krzar/laravel-translation-generator
```

## Usage

> [!NOTE]
> Remember, this package supports Laravel Prompts. So you can skip commands arguments and just answer the questions.

### Generate new translation

Generate new translation files for `es` language.
```bash
php artisan make:translation es
```

If the file exists, it will be completed with the missing keys.

Files and keys will be copied based on the fallback locale specified in the app configuration.

You can change fallback locale.

**If you have published any translations from other packages the command will ask you if you want to generate
new language translations for them as well.**

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

This will generate new php file `common.php` in every language folder (except packages translations folders).
