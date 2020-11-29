<?php


namespace Beta\Composer\Interfaces;


interface ResultOperationInterface
{
    /**
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * @return string
     */
    public function getErrorMessage(): string;

    /**
     * @return mixed
     */
    public function throw();
}
