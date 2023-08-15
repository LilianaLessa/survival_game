<?php

declare(strict_types=1);

namespace App\System;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class Kernel
{
    static private ?ContainerBuilder $container;

    public static function getContainer(): ContainerBuilder
    {
        self::$container ??= self::loadContainer();

        return self::$container;
    }

    private static function loadContainer(): ContainerBuilder
    {
        //dependency injection config
        $container = new ContainerBuilder();
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../DependencyInjection'));
        $loader->load('services.php');

        return $container;
    }
}
