<?php

namespace Package\Manager;

use Bitrix\Main\Application;
use Package\Manager\Interfaces\AutoloaderInterface;
use Package\Manager\Traits\ComposerCommandTrait;
use Package\Manager\Traits\ConfigTrait;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;

class ComposerAutoloader implements AutoloaderInterface
{
    use ConfigTrait;
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

    private function __construct() {}
    private function __clone() {}

    public static function instance(): self
    {
        if (static::$instance instanceof ComposerAutoloader) {
            return static::$instance;
        }

        return static::$instance = new static();
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
            $module = "/bitrix/modules/{$module}";
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
            $module = "/local/modules/{$module}";
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
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function addClassMap(string ...$relativePathList): bool
    {
        $config = $this->getComposerConfig();
        $classMap = $config['autoload']['classmap'] ?? [];
        $needUpdate = false;
        foreach ($relativePathList as $relativePath) {
            if (!in_array($relativePath, $classMap)) {
                $needUpdate = true;
                $classMap[] = $relativePath;
            }
        }

        if (!$needUpdate) {
            return false;
        }

        $config['autoload']['classmap'] = $classMap;
        $result = $this->updateComposerConfig($config);
        if ($result) {
            $this->needUpdateDump = true;
        }

        return $result;
    }

    /**
     * @param array $namespaceList
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function addNamespace(array $namespaceList): bool
    {
        $config = $this->getComposerConfig();
        $psr4 = $config['autoload']['psr-4'] ?? [];
        $needUpdate = false;
        foreach ($namespaceList as $namespace => $relativePath) {
            if (!isset($psr4[$namespace]) || $psr4[$namespace] !== $relativePath) {
                $needUpdate = true;
                $psr4[$namespace] = $relativePath;
            }
        }

        if (!$needUpdate) {
            return false;
        }

        $config['autoload']['psr-4'] = $psr4;
        $result = $this->updateComposerConfig($config);
        if ($result) {
            $this->needUpdateDump = true;
        }

        return $result;
    }

    /**
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws CommandException
     */
    public function include(): bool
    {
        foreach ($this->needInclude as $includeFile) {
            if(file_exists($includeFile)) {
                require_once $includeFile;
            }
        }

        $this->needInclude = [];
        ComposerPackageManager::instance()->run();
        if ($this->needUpdateDump) {
            $this->dumpAutoload();
        }

        $file = $this->getVendorDir().'/autoload.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }

        return false;
    }
}
