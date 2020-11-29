<?php


namespace Package\Manager\Interfaces;


interface AutoloaderInterface
{
    /**
     * @return bool
     */
    public function include(): bool;
}
