<?php

declare(strict_types=1);

namespace App\System;

use App\Engine\System\GameSystemInterface;
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

    /** @return GameSystemInterface */
    public static function getRegisteredGameSystemInstances(): array
    {
        //todo make it work.

        $container = self::getContainer();

        $gameSystemClasses = array_filter(
            get_declared_classes(),
            fn ($c) => in_array(GameSystemInterface::class, class_implements($c))
        );

        $instances = [];
        foreach ($gameSystemClasses as $gameSystemClass)  {
            $instance = $container->get($gameSystemClass);
            $instance && $instances[] = $instance;
        }

        return $instances;
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
