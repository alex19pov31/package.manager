<?php


namespace Package\Manager;


use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;

class ComposerConfig
{
    protected $workDir;
    protected $composerPath;
    protected $composerConfig;

    /**
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getWorkDir(): string
    {
        if (!empty($this->workDir)) {
            return (string)$this->workDir;
        }

        $defaultPath = Application::getDocumentRoot();
        return $this->workDir = Option::get('package.manager', 'WORK_DIR', $defaultPath);
    }

    /**
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getVendorDir(): string
    {
        return $this->getWorkDir().'/vendor';
    }

    /**
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getComposerPath(): string
    {
        if (!empty($this->composerPath)) {
            return (string)$this->composerPath;
        }

        return $this->composerPath = Option::get('package.manager', 'COMPOSER_PATH', '/bin/composer');
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getComposerConfig(): array
    {
        $configFile = $this->getWorkDir().'/composer.json';
        if (!file_exists($configFile)) {
            return [];
        }

        $content = file_get_contents($configFile);
        return json_decode($content, true);
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getRequiredPackages(): array
    {
        return $this->getComposerConfig()['require'] ?? [];
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getClassmapList(): array
    {
        return $this->getComposerConfig()['autoload']['classmap'] ?? [];
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getNamespaceList(): array
    {
        return $this->getComposerConfig()['autoload']['psr-4'] ?? [];
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function getDevRequiredPackages(): array
    {
        return $this->getComposerConfig()['dev-require'] ?? [];
    }

    /**
     * @param string $packageName
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function packageInRequired(string $packageName): bool
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
    public function hasClassmapPath(string $relativePath): bool
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
    public function hasNamespace(string $namespace, string $relativePath): bool
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
    public function packageInDevRequired(string $packageName): bool
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
    public function updateComposerConfig(array $config): bool
    {
        $configFile = $this->getWorkDir().'/composer.json';
        $result = (bool)file_put_contents($configFile, json_encode($config,  JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        if ($result === true) {
            $this->composerConfig = $config;
        }

        return $result;
    }

    /**
     * @param string $packageName
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function packageIsInstalled(string $packageName): bool
    {
        if (!$this->packageInRequired($packageName)) {
            return false;
        }

        $packageDir = $this->getVendorDir().'/'.$packageName;
        return (bool)is_dir($packageDir);
    }
}
