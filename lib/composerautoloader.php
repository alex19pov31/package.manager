<?php

namespace Package\Manager;

use Bitrix\Main\Application;
use Package\Manager\Interfaces\AutoloaderInterface;
use Package\Manager\Traits\ComposerCommandTrait;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;

class ComposerAutoloader implements AutoloaderInterface
{
    use ComposerCommandTrait;

    /**
     * @var ComposerAutoloader
     */
    private static $instance;
    /**
     * @var bool
     */
    private $needUpdateDump = false;
    /**
     * @var array
     */
    private $needInclude = [];
    /**
     * @var ComposerConfig
     */
    private $config;
    /**
     * @var ComposerPackageManager
     */
    private $packageManager;

    /**
     * @var array
     */
    private $addClassmap = [];

    /**
     * @var array
     */
    private $addNamespace = [];

    public function __construct(
        ComposerConfig $config,
        ComposerPackageManager $packageManager
    ){
        $this->config = $config;
        $this->packageManager = $packageManager;
    }

    /**
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function getComposerPath(): string
    {
        return $this->config->getComposerPath();
    }

    /**
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    protected function getWorkDir(): string
    {
        return $this->config->getWorkDir();
    }

    /**
     * @param string ...$moduleList
     * @return $this
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function registerBitrixModule(string ...$moduleList): self
    {
        foreach ($moduleList as &$module) {
            $module = "bitrix/modules/{$module}";
        }

        unset($module);
        $this->addClassMap(...$moduleList);
        return $this;
    }

    /**
     * @param string ...$moduleList
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function registerLocalModule(string ...$moduleList): string
    {
        $documentRoot = Application::getDocumentRoot();
        foreach ($moduleList as &$module) {
            $module = "local/modules/{$module}";
            $includeFile = $documentRoot.$module."/include.php";
            if (file_exists($includeFile) && !in_array($includeFile, $this->needInclude)) {
                $this->needInclude[] = $includeFile;
            }
        }

        unset($module);
        $this->addClassMap(...$moduleList);
        return $this;
    }


    /**
     * @param string ...$relativePathList
     * @return bool
     */
    public function addClassMap(string ...$relativePathList): bool
    {
        foreach ($relativePathList as $relativePath) {
            $this->addClassmap[] = $relativePath;
        }
        return true;
    }

    /**
     * @param array $namespaceList
     * @return bool
     */
    public function addNamespace(array $namespaceList): bool
    {
        foreach ($namespaceList as $namespace => $relativePath) {
            $this->addNamespace[$namespace] = $relativePath;
        }

        return true;
    }

    /**
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws CommandException
     */
    private function updateAutoload()
    {
        $config = $this->config->getComposerConfig();
        $classMap = $config['autoload']['classmap'] ?? [];
        $psr4 = $config['autoload']['psr-4'] ?? [];
        $needUpdate = false;

        foreach ($this->addClassmap as $relativePath) {
            if (!in_array($relativePath, $classMap)) {
                $needUpdate = true;
                $classMap[] = $relativePath;
            }
        }

        foreach ($this->addNamespace as $namespace => $relativePath) {
            if (!isset($psr4[$namespace]) || $psr4[$namespace] !== $relativePath) {
                $needUpdate = true;
                $psr4[$namespace] = $relativePath;
            }
        }

        if ($needUpdate) {
            if (!empty($classMap)) {
                $config['autoload']['classmap'] = $classMap;
            }

            if (!empty($psr4)) {
                $config['autoload']['psr-4'] = $psr4;
            }

            $this->config->updateComposerConfig($config);
            $this->dumpAutoload();
        }

        $this->addClassmap = [];
        $this->addNamespace = [];
    }

    /**
     * @param bool $checkDependency
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws CommandException
     */
    public function include(bool $checkDependency = false): bool
    {
        foreach ($this->needInclude as $includeFile) {
            if(file_exists($includeFile)) {
                require_once $includeFile;
            }
        }

        if ($checkDependency) {
            $this->needInclude = [];
            $this->packageManager->run();
            $this->updateAutoload();
        }

        $file = $this->config->getVendorDir().'/autoload.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }

        return false;
    }
}
