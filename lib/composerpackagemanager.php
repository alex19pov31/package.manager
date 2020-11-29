<?php


namespace Package\Manager;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Package\Manager\Interfaces\PackageManagerInterface;
use Package\Manager\Traits\ComposerCommandTrait;
use Package\Manager\Traits\ConfigTrait;

class ComposerPackageManager implements PackageManagerInterface
{
    use ConfigTrait;
    use ComposerCommandTrait;

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

    /**
     * @return PackageManagerInterface
     */
    public static function instance(): PackageManagerInterface
    {
        if (static::$instance instanceof PackageManagerInterface) {
            return static::$instance;
        }

        return static::$instance = new static();
    }

    /**
     * @param string $package
     * @param string|null $version
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function add(string $package, string $version = null)
    {
        if ($this->isInstalled($package)) {
            return;
        }

        $key = $package;
        if ($version !== null) {
            $package = "{$package} {$version}";
        }

        $this->addList[$key] = $package;
    }

    /**
     * @param string $package
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function delete(string $package)
    {
        if ($this->isRequired($package)) {
            $this->deleteList[$package] = $package;
        }
    }

    /**
     * @param string $package
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function isInstalled(string $package): bool
    {
        return $this->packageIsInstalled($package);
    }


    /**
     * @return void
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws CommandException
     *
     */
    public function run()
    {
        foreach ($this->addList as $package) {
            if (!$this->isInstalled($package)) {
                $this->requirePackage($package);
            }
        }

        foreach ($this->deleteList as $package) {
            if ($this->isInstalled($package)) {
                $this->removePackage($package);
            }
        }

        $this->addList = [];
        $this->deleteList = [];
    }

    /**
     * @param string $package
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function isRequired(string $package): bool
    {
        return $this->packageInRequired($package);
    }
}
