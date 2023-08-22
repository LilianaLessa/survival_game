<?php

declare(strict_types=1);

namespace App\Engine\System\ItemCollection;

use App\Engine\Component\ActionQueueComponentInterface;
use App\Engine\Component\AggroQueue;
use App\Engine\Component\BehaviorCollection;
use App\Engine\Component\CurrentBehavior;
use App\Engine\Component\HitByEntity;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MsTimeFromLastActivation;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\System\AISystemInterface;
use App\System\AI\Behavior\BehaviorPreset;
use App\System\AI\Behavior\BehaviorTrigger;
use App\System\AI\Behavior\EffectHandlers\BehaviorEffectHandlerInterface;
use App\System\AI\Behavior\EffectHandlers\BehaviorTriggerType;
use App\System\AI\TriggerValueEvaluatorWrapper;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\Kernel;

class EntityBehaviorSystem implements AISystemInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function process(): void
    {
        //return;
        $behavioralEntities = $this->entityManager->getEntitiesWithComponents(
            BehaviorCollection::class,
            MapPosition::class
        );

        /**
         * @var BehaviorCollection $behaviorCollection
         */
        foreach ($behavioralEntities as $entityId => [$behaviorCollection,]) {
            /** @var Entity $entityToBeEvaluated */
            $entityToBeEvaluated = $this->entityManager->getEntityById($entityId);
            $triggeredBehaviors = $this->getTriggeredBehaviors($entityToBeEvaluated, $behaviorCollection);

            //todo if multiple triggered, decide which one has priority and trigger it
            foreach ($triggeredBehaviors as $triggeredBehavior) {
                $behaviorControlComponents = $triggeredBehavior->isSilent() ? [] : [
                    new MsTimeFromLastActivation(
                        (int) floor(microtime(true) * 1000)
                    ),
                    new CurrentBehavior($triggeredBehavior)
                ];

                Dispatcher::dispatch(
                    new UiMessageEvent(
                        sprintf(
                            "%sBehavior triggered: %s\n",
                            $triggeredBehavior->isSilent() ? 'Silent' : '',
                            $triggeredBehavior->getName(),
                        )
                    )
                );

                $this->entityManager->updateEntityComponents(
                    $entityId,
                    ...$behaviorControlComponents
                );

                $this->executeBehaviorEffects($entityToBeEvaluated, $triggeredBehavior);
            }
        }
    }

    /** @return TriggerValueEvaluatorWrapper[] */
    private function loadTriggerEvaluators(BehaviorTrigger ...$triggers): array
    {
        //todo fix: if the behavior trigger handlers are not defined, it stops all the behavior processing.
        $evaluators = [];
        foreach ($triggers as $trigger) {
            $triggerType = $trigger->getName();
            $triggerValue = $trigger->getValue();
            $evaluator = match ($triggerType) {
                //todo maybe introduce a factory for this, or even make the enum responsible for it?
                //todo maybe it can return a closure to get and evaluate, returning boolean if the trigger is triggered.
                BehaviorTriggerType::ARE_ACTION_QUEUES_EMPTY =>
                    function (Entity $e) use ($triggerValue) {
                        $nonEmptyQueues = array_filter(
                            $e->getComponents(),
                            fn ($q) => $q instanceof ActionQueueComponentInterface && !$q->isQueueEmpty()
                        );


                        return $triggerValue === empty($nonEmptyQueues);
                    },
                BehaviorTriggerType::MS_TIME_FROM_LAST_ACTIVATION =>
                    function (Entity $e) use ($triggerValue) {
                        /** @var ?MsTimeFromLastActivation $component */
                        $component = $e->getComponent(MsTimeFromLastActivation::class);
                        $lastActivationMsTime = $component?->getMsTime();
                        $currentMsTime = (int) floor(microtime(true) * 1000);
                        return $lastActivationMsTime === null
                            || ($currentMsTime - $lastActivationMsTime >= $triggerValue);
                    },
                BehaviorTriggerType::IS_TARGET_OF_ATTACK =>
                    function (Entity $e) use ($triggerValue) {
                        return ((bool)$triggerValue) && $e->getComponent(HitByEntity::class) !== null;
                    },
                BehaviorTriggerType::IS_AGGRO_QUEUE_EMPTY =>
                function (Entity $e) use ($triggerValue) {
                    /** @var ?AggroQueue $aggroQueue */
                    $aggroQueue = $e->getComponent(AggroQueue::class);

                    return ($aggroQueue?->isEmpty() ?? true) === (bool)$triggerValue;
                },
                default => null
            };

            $evaluator && $evaluators[$triggerType->value] = new TriggerValueEvaluatorWrapper($evaluator);
        }

        return $evaluators;
    }

    /**
     * @return BehaviorPreset[]
     */
    private function getTriggeredBehaviors(?Entity $entityToBeEvaluated, BehaviorCollection $behaviorCollection): array
    {
        /** @var ?CurrentBehavior $currentEntityBehavior */
        $currentEntityBehavior = $entityToBeEvaluated->getComponent(CurrentBehavior::class);

        //check first if any transition is possible from the current behavior.
        if ($currentEntityBehavior) {
            $possibleTransitions = $currentEntityBehavior->getBehaviorPreset()->getTransitions()->getTo();
            if (count($possibleTransitions)) {
               foreach ($possibleTransitions as $possibleTransition) {
                   $triggerValueEvaluators = $this->loadTriggerEvaluators(...$possibleTransition->getTriggers());
                   foreach ($triggerValueEvaluators as $evaluator) {
                       if (!$evaluator->evaluateTrigger($entityToBeEvaluated)) {
                           continue 2;
                       }
                   }

                   $triggered = array_filter(
                       $behaviorCollection->getBehaviors(),
                       fn ($b) => $b->getName() === $possibleTransition->getBehaviorName()
                   );

                   return $triggered;
               }
            }
        }

        //if not, check all the collection.

        $triggeredBehaviors = [];
        foreach ($behaviorCollection->getBehaviors() as $behavior) {

            //check the triggers for $behavior
            $triggerValueEvaluators = $this->loadTriggerEvaluators(...$behavior->getTriggers());
            foreach ($triggerValueEvaluators as $evaluator) {
                if (!$evaluator->evaluateTrigger($entityToBeEvaluated)) {
                    continue 2;
                }
            }

            //check if transition from the current behavior is possible;
            if (
                $behavior->getTransitions()
                    ->canTransitionFrom($currentEntityBehavior?->getBehaviorPreset())
                && ($currentEntityBehavior?->getBehaviorPreset()->getTransitions()
                    ->canTransitionTo($behavior) ?? true)
            ) {
                $triggeredBehaviors[] = $behavior;
            }
        }

        return $triggeredBehaviors;
    }

    private function executeBehaviorEffects(Entity $entityToApplyEffects, BehaviorPreset $triggeredBehavior): void
    {
        /** @var BehaviorEffectHandlerInterface[]|string[] $effectHandlerClasses */
        $effectHandlerClasses = array_filter(
            get_declared_classes(),
            fn ($c) => in_array(BehaviorEffectHandlerInterface::class, class_implements($c))
        );

        $effects = $triggeredBehavior->getEffectConfigs();

        foreach ($effects as $effect) {
            $effectType = $effect->getBehaviorEffectType();
            foreach ($effectHandlerClasses as $effectHandlerClass) {
                if ($effectHandlerClass::shouldHandle($effectType)) {
                    $handler = Kernel::getContainer()->get($effectHandlerClass);
                    //instantiate parameters;
                    $parameterConfigs = $effect->getEffectParameterConfigs();
                    $handler->handle(
                        $entityToApplyEffects,
                        ...$effectHandlerClass::buildEffectParameters(...$parameterConfigs)
                    );
                   // break;
                }
            }
        }
    }
}
