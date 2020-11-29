<?php

namespace Beta\Composer\Traits;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;

trait ConfigTrait
{
    protected static $vendorDir;
    protected static $composerPath;
    protected static $composerConfig;

    protected function getVendorDir(): string
    {
        if (!empty(static::$vendorDir)) {
            return (string)static::$vendorDir;
        }

        $defaultPath = Application::getDocumentRoot().'/local/vendor';
        return static::$vendorDir = Option::get('package.manager', 'VENDOR_DIR', $defaultPath);
    }

    protected function getComposerPath(): string
    {
        if (!empty(static::$composerPath)) {
            return (string)static::$composerPath;
        }

        return static::$composerPath = Option::get('package.manager', 'COMPOSER_PATH', '/bin/composer');
    }

    protected function getComposerConfig(): array
    {
        if (!empty(static::$composerConfig)) {
            return static::$composerConfig;
        }

        $configFile = $this->getComposerPath().'/composer.json';
        if (!file_exists($configFile)) {
            return [];
        }

        $content = file_get_contents($this->getComposerPath().'/../composer.json');
        return static::$composerConfig = json_decode($content, true);
    }

    protected function getRequiredPackages(): array
    {
        return $this->getComposerConfig()['require'] ?? [];
    }

    protected function getDevRequiredPackages(): array
    {
        return $this->getComposerConfig()['dev-require'] ?? [];
    }

    protected function packageInRequired(string $packageName): bool
    {
        if (isset($this->getRequiredPackages()[$packageName])) {
            return true;
        }

        return false;
    }

    protected function packageInDevRequired(string $packageName): bool
    {
        if (isset($this->getDevRequiredPackages()[$packageName])) {
            return true;
        }

        return false;
    }

    protected function packageIsInstalled(string $packageName): bool
    {
        if (!$this->packageInRequired($packageName)) {
            return false;
        }

        $packageDir = $this->getVendorDir().$packageName;
        return (bool)is_dir($packageDir);
    }
}