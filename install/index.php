<?

IncludeModuleLangFile(__FILE__);

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;

class package_manager extends CModule
{
    public $MODULE_ID = "package.manager";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $errors;

    public function __construct()
    {
        $this->MODULE_VERSION = "0.0.1";
        $this->MODULE_VERSION_DATE = "2020-11-28 22:05:41";
        $this->MODULE_NAME = "Управление пакетами";
        $this->MODULE_DESCRIPTION = "Управление пакетами: composer, git...";
    }

    /**
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function DoInstall(): bool
    {
        $composerPath = Option::get($this->MODULE_ID, 'COMPOSER_PATH', null);
        if (empty($composerPath)) {
            $this->installComposer();
        }

        ModuleManager::RegisterModule($this->MODULE_ID);
        return true;
    }

    /**
     * @return bool
     * @throws ArgumentOutOfRangeException
     */
    private function installComposer(): bool
    {
        $documentRoot = Application::getDocumentRoot();
        copy('https://getcomposer.org/installer', "{$documentRoot}/local/composer-setup.php");

        $output = null;
        $result = null;

        $composerPath = "{$documentRoot}/local/composer.phar";
        $commandList = [
            "php {$documentRoot}/local/composer-setup.php --install-dir={$documentRoot}/local",
        ];
        unlink("{$documentRoot}/local/composer-setup.php");
        exec(implode(' && ', $commandList), $output, $result);
        if (!file_exists($composerPath) || $result === 1) {
            return false;
        }

        Option::set($this->MODULE_ID, 'COMPOSER_PATH', $composerPath);
        Option::set($this->MODULE_ID, 'WORK_DIR', "{$documentRoot}/local");

        return true;
    }

    /**
     * @return bool
     */
    public function DoUninstall(): bool
    {
        ModuleManager::UnRegisterModule($this->MODULE_ID);
        return true;
    }
}
