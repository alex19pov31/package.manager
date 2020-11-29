<?php

namespace Beta\Composer;

use Beta\Composer\Interfaces\AutoloaderInterface;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;

class ComposerAutoloader implements AutoloaderInterface
{
    /**
     * @var ComposerAutoloader
     */
    private static $instance;

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
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    private function getVendorDir(): string
    {
        $defaultPath = Application::getDocumentRoot().'/local/vendor';
        return Option::get('beta.composer', 'VENDOR_DIR', $defaultPath);
    }

    /**
     * @param string $module
     * @return $this
     */
    public function registerBitrixModule(string $module): self
    {
        return $this;
    }

    /**
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function include(): bool
    {
        ComposerPackageManager::instance()->run();

        $file = $this->getVendorDir().'/autoload.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }

        return false;
    }
}
