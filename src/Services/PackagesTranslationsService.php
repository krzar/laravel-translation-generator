<?php

namespace Krzar\LaravelTranslationGenerator\Services;

use Illuminate\Support\Collection;

class PackagesTranslationsService
{
    private const VENDOR_PATH = 'vendor';

    public function findPackages(): ?Collection
    {
        $vendorPath = lang_path(self::VENDOR_PATH);

        if (! file_exists($vendorPath)) {
            return null;
        }

        $packages = collect(scandir($vendorPath))->filter(
            fn (string $fileName) => $fileName !== '.' && $fileName !== '..'
        );

        return $packages->count() > 0 ? $packages : null;
    }

    public function getPhpTranslationsFiles(string $fallback): Collection
    {
        return $this->findPackages()->flatMap(
            fn (string $package) => $this->getPackagePhpTranslationsFiles($package, $fallback)
        );
    }

    private function getPackagePhpTranslationsFiles(string $package, string $fallback): Collection
    {
        $path = lang_path(sprintf('%s/%s/%s', self::VENDOR_PATH, $package, $fallback));

        return collect(scandir($path))->filter(
            fn (string $fileName) => $fileName !== '.' && $fileName !== '..'
        )->map(fn (string $fileName) => sprintf(
            '%s/%s/%s/%s',
            self::VENDOR_PATH,
            $package,
            $fallback,
            $fileName
        ));
    }
}
