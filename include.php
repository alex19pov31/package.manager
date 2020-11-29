<?php

use Beta\Composer\ComposerPackageManager;
use Beta\Composer\ComposerAutoloader;

$packageManager = ComposerPackageManager::instance();
$packageManager->add('illuminate/collections');

$autoloader = ComposerAutoloader::instance();
$autoloader
    ->registerBitrixModule('catalog')
    ->registerBitrixModule('iblock')
    ->registerBitrixModule('sale')
    ->registerBitrixModule('hightloadblock')
    ->include();
