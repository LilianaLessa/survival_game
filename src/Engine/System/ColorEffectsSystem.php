<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\ColorEffect;
use App\Engine\Component\MapSymbol;
use App\Engine\Entity\EntityManager;
use App\System\ConsoleColor;

class ColorEffectsSystem implements WorldSystemInterface
{

    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function process(): void
    {
        //remove expired color effects:

        $entitiesWithColorEffect = $this->entityManager->getEntitiesWithComponents(
            ColorEffect::class
        );

        /**
         * @var ColorEffect $colorEffect
         */
        foreach ($entitiesWithColorEffect as $entityId => [$colorEffect]) {
            if ($colorEffect->isExpired()) {
                $this->entityManager->removeComponentsFromEntity($entityId, $colorEffect);
            }
        }

//        //todo debug tests
//        $entities = $this->entityManager->getEntityCollection();
//        foreach ($entities as $entityId => $entity) {
//            if (rand(0,100) < 30) {
//                $this->entityManager->updateEntityComponents(
//                    $entityId,
//                    new ColorEffect(150, ConsoleColor::Red->value),
//                );
//            }
//        }
    }
}
