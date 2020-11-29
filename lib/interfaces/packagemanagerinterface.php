<?php


namespace Beta\Composer\Interfaces;


interface PackageManagerInterface
{
    /**
     * @param string $package
     * @param string|null $version
     * @return void
     */
    public function add(string $package, string $version = null);

    /**
     * @param string $package
     * @return void
     */
    public function delete(string $package);

    /**
     * @param string $package
     * @param string|null $version
     * @return mixed
     */
    public function findPackage(string $package, string $version = null);

    /**
     * @param string $package
     * @return bool
     */
    public function isInstalled(string $package): bool;

    /**
     * @param string $package
     * @return bool
     */
    public function isRequired(string $package): bool;

    /**
     * @return ResultOperationInterface
     */
    public function run(): ResultOperationInterface;
}
