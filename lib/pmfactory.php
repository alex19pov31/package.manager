<?php


namespace Package\Manager;


class PMFactory
{
    /**
     * @var PMFactory
     */
    private static $instance;
    /**
     * @var ComposerConfig
     */
    private $composerConfig;
    /**
     * @var ComposerAutoloader
     */
    private $composerAutoloader;
    /**
     * @var ComposerPackageManager
     */
    private $composerPackageManager;

    private function __construct()
    {
        $this->composerConfig = new ComposerConfig();
    }

    private function __clone() {}

    /**
     * @return static
     */
    public static function instance(): self
    {
        if (static::$instance instanceof PMFactory) {
            return static::$instance;
        }

        return static::$instance = new static();
    }

    /**
     * @return ComposerAutoloader
     */
    public function getComposerAutoloader(): ComposerAutoloader
    {
        if ($this->composerAutoloader instanceof ComposerAutoloader) {
            return $this->composerAutoloader;
        }

        return $this->composerAutoloader = new ComposerAutoloader(
            $this->composerConfig,
            $this->getComposerPackageManager()
        );
    }

    /**
     * @return ComposerPackageManager
     */
    public function getComposerPackageManager(): ComposerPackageManager
    {
        if ($this->composerPackageManager instanceof ComposerPackageManager) {
            return $this->composerPackageManager;
        }

        return $this->composerPackageManager = new ComposerPackageManager($this->composerConfig);
    }
}
