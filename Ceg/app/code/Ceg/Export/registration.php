<?php
declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Ceg_Export',
    __DIR__
);
// ignore added because of core rewritten param
// @codingStandardsIgnoreStart
// Register the custom Filesystem FileDriver that calculates correct relative paths
$_SERVER[\Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DRIVERS] = [
    'file' => \Ceg\Export\Filesystem\Driver\File::class,
];
// @codingStandardsIgnoreEnd