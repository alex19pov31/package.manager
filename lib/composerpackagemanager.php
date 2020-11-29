<?php


namespace Package\Manager;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Package\Manager\Interfaces\PackageManagerInterface;
use Package\Manager\Traits\ComposerCommandTrait;

class ComposerPackageManager implements PackageManagerInterface
{
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
    /**
     * @var ComposerConfig
     */
    private $config;

    public function __construct(ComposerConfig $config)
    {
        $this->config = $config;
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
        return $this->config->packageIsInstalled($package);
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
        return $this->config->packageInRequired($package);
    }
}
