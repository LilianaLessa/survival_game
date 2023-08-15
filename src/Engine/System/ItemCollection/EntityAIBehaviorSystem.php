<?php

declare(strict_types=1);

namespace App\Engine\System\ItemCollection;

use App\Engine\Component\AiBehaviorCollection;
use App\Engine\Component\MapPosition;
use App\Engine\Entity\EntityManager;
use App\Engine\System\AISystemInterface;

class EntityAIBehaviorSystem implements AISystemInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function process(): void
    {
        $behavioralEntities = $this->entityManager->getEntitiesWithComponents(
            AIBehaviorCollection::class,
            MapPosition::class
        );

        /**
         * @var AIBehaviorCollection $behaviorCollection
         * @var MapPosition $mapPosition
         */
        foreach ($behavioralEntities as $entityId => [$behaviorCollection, $mapPosition]) {
            $targetEntity = $this->entityManager->getEntityById($entityId);
            $triggeredBehaviours = [];
            foreach ($behaviorCollection as $behavior) {
                //evaluate triggers.
                //check current entity behavior and check if any transition is possible based on its triggers.
                //if there is a transition possible, do transition to the state and
                //   stop the evaluation for the current entity.
                //if not, check the triggers for $behavior
                //if they match, add it to $triggeredBehavior
            }

            foreach ($triggeredBehaviours as $triggeredBehavior) {
                ////calculate the effect parameters
                //execute the behavior effects.
            }
        }
    }
}
