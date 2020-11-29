<?

IncludeModuleLangFile(__FILE__);
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;

class beta_composer extends CModule
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

    public function DoInstall()
    {
        $vendorDir = Option::get($this->MODULE_ID, 'VENDOR_DIR', null);
        $composerPath = Option::get($this->MODULE_ID, 'COMPOSER_PATH', null);
        if (empty($composerPath)) {
            $this->installComposer();
        }

        ModuleManager::RegisterModule($this->MODULE_ID);
        return true;
    }

    private function installComposer(): bool
    {
        $documentRoot = Applicaton::getDocumentRoot();
        copy('https://getcomposer.org/installer', "{$documentRoot}/local/composer-setup.php");
        require_once "{$documentRoot}/local/composer-setup.php";
        unlink("{$documentRoot}/local/composer-setup.php");
        $composerPath = "{$documentRoot}/local/composer.phar";
        if (!file_exists($composerPath)) {
            return false;
        }

        Option::set($this->MODULE_ID, 'COMPOSER_PATH', $composerPath);
        Option::set($this->MODULE_ID, 'VENDOR_DIR', "{$documentRoot}/local/vendor");

        return true;
    }

    public function DoUninstall()
    {
        /*$this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();*/
        ModuleManager::UnRegisterModule($this->MODULE_ID);
        return true;
    }

    public function InstallDB()
    {
        global $DB;
        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/".$this->MODULE_ID."/install/db/install.sql");
        if (!$this->errors) {

            return true;
        } else {
            return $this->errors;
        }

    }

    public function UnInstallDB()
    {
        global $DB;
        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/".$this->MODULE_ID."/install/db/uninstall.sql");
        if (!$this->errors) {
            return true;
        } else {
            return $this->errors;
        }

    }

    public function InstallEvents()
    {
        return true;
    }

    public function UnInstallEvents()
    {
        return true;
    }

    public function InstallFiles()
    {
        return true;
    }

    public function UnInstallFiles()
    {
        return true;
    }
}
