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
        return self::getAllRegisteredConcreteInstances(GameSystemInterface::class);
    }

    /** @return object[] */
    public static function getAllRegisteredConcreteInstances(string $abstractOrInterface): array
    {
        $container = self::getContainer();

        $serviceIds = $container->getServiceIds();

        $instances = [];
        foreach ($serviceIds as $serviceId) {
            if (class_exists($serviceId) && is_subclass_of($serviceId, $abstractOrInterface)) {
                    $instances[] = $container->get($serviceId);
            }
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
