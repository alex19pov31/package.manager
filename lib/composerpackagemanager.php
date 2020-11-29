<?php


namespace Beta\Composer;


use Beta\Composer\Interfaces\AutoloaderInterface;
use Beta\Composer\Interfaces\PackageManagerInterface;
use Beta\Composer\Interfaces\ResultOperationInterface;
use Bitrix\Main\Config\Option;

class ComposerPackageManager implements PackageManagerInterface
{
    /**
     * @var array
     */
    private $addList = [];
    /**
     * @var array
     */
    private $deleteList = [];
    /**
     * @var PackageManagerInterface
     */
    private static $instance;

    private function __construct() {}
    private function __clone() {}

    public static function instance(): PackageManagerInterface
    {
        if (static::$instance instanceof PackageManagerInterface) {
            return static::$instance;
        }

        return static::$instance = new static();
    }

    private function getComposerPath(): string
    {
        return Option::get('beta.composer', 'COMPOSER_PATH', '/bin/composer');
    }

    public function add(string $package, string $version = null)
    {
        if ($this->isRequired($package)) {
            return;
        }

        $key = $package;
        if ($version !== null) {
            $package = "{$package} {$version}";
        }

        $this->addList[$key] = $package;
    }

    public function delete(string $package)
    {
        if ($this->isRequired($package)) {
            $this->deleteList[$package] = $package;
        }
    }

    public function findPackage(string $package, string $version = null)
    {
        // TODO: Implement findPackage() method.
    }

    public function isInstalled(string $package): bool
    {
        // TODO: Implement isInstalled() method.
        return false;
    }

    /**
     * @return ResultOperationInterface
     */
    public function run(): ResultOperationInterface
    {
        $commandList = [];
        $composerPath = $this->getComposerPath();

        foreach ($this->addList as $package) {
            if (!$this->isInstalled($package)) {
                $commandList[] = "{$composerPath} require {$package}";
            }
        }

        foreach ($this->deleteList as $package) {
            if ($this->isInstalled($package)) {
                $commandList[] = "{$composerPath} remove {$package}";
            }
        }

        $commandStr = implode(' && ', $commandList);
        if (empty($commandStr)) {
            return;
        }

        try {
            exec($commandStr);
            $this->addList = [];
            $this->deleteList = [];
        } catch (\Throwable $e) {
            return new ResultOperation(false, $e->getMessage());
        }

        return new ResultOperation(true);
    }

    public function isRequired(string $package): bool
    {
        return false;
    }
}
