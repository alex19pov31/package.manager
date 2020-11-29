<?php

namespace Package\Manager\Traits;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;

trait ConfigTrait
{
    protected static $workDir;
    protected static $composerPath;
    protected static $composerConfig;

    /**
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function getWorkDir(): string
    {
        if (!empty(static::$workDir)) {
            return (string)static::$workDir;
        }

        $defaultPath = Application::getDocumentRoot().'/local';
        return static::$workDir = Option::get('package.manager', 'WORK_DIR', $defaultPath);
    }

    /**
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function getVendorDir(): string
    {
        return $this->getWorkDir().'/vendor';
    }

    /**
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function getComposerPath(): string
    {
        if (!empty(static::$composerPath)) {
            return (string)static::$composerPath;
        }

        return static::$composerPath = Option::get('package.manager', 'COMPOSER_PATH', '/bin/composer');
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function getComposerConfig(): array
    {
        if (!empty(static::$composerConfig)) {
            return static::$composerConfig;
        }

        $configFile = $this->getWorkDir().'/composer.json';
        if (!file_exists($configFile)) {
            return [];
        }

        $content = file_get_contents($configFile);
        return static::$composerConfig = json_decode($content, true);
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function getRequiredPackages(): array
    {
        return $this->getComposerConfig()['require'] ?? [];
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function getClassmapList(): array
    {
        return $this->getComposerConfig()['autoload']['classmap'] ?? [];
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function getNamespaceList(): array
    {
        return $this->getComposerConfig()['autoload']['psr-4'] ?? [];
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function getDevRequiredPackages(): array
    {
        return $this->getComposerConfig()['dev-require'] ?? [];
    }

    /**
     * @param string $packageName
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function packageInRequired(string $packageName): bool
    {
        if (isset($this->getRequiredPackages()[$packageName])) {
            return true;
        }

        return false;
    }

    /**
     * @param string $relativePath
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function hasClassmapPath(string $relativePath): bool
    {
        return in_array($relativePath, $this->getClassmapList());
    }

    /**
     * @param string $namespace
     * @param string $relativePath
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function hasNamespace(string $namespace, string $relativePath): bool
    {
        $namespaceList = $this->getNamespaceList();
        return isset($namespaceList[$namespace]) && $namespaceList[$namespace] === $relativePath;
    }

    /**
     * @param string $packageName
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function packageInDevRequired(string $packageName): bool
    {
        if (isset($this->getDevRequiredPackages()[$packageName])) {
            return true;
        }

        return false;
    }

    /**
     * @param array $config
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    private function updateComposerConfig(array $config): bool
    {
        $configFile = $this->getWorkDir().'/composer.json';
        $result = (bool)file_put_contents($configFile, json_encode($config));
        if ($result === true) {
            static::$composerConfig = $config;
        }

        return $result;
    }

    /**
     * @param string $packageName
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function packageIsInstalled(string $packageName): bool
    {
        if (!$this->packageInRequired($packageName)) {
            return false;
        }

        $packageDir = $this->getVendorDir().$packageName;
        return (bool)is_dir($packageDir);
    }
}
