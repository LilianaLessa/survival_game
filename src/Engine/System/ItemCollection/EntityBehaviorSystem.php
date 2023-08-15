<?php

declare(strict_types=1);

namespace App\Engine\System\ItemCollection;

use App\Engine\Component\ActionQueueComponentInterface;
use App\Engine\Component\BehaviorCollection;
use App\Engine\Component\CurrentBehavior;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MsTimeFromLastBehaviorActivation;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\System\AISystemInterface;
use App\System\AI\Behavior\BehaviorPreset;
use App\System\AI\Behavior\EffectHandlers\BehaviorEffectHandlerInterface;
use App\System\AI\Behavior\EffectHandlers\BehaviorTriggerType;
use App\System\AI\TriggerValueEvaluatorWrapper;
use App\System\Kernel;

class EntityBehaviorSystem implements AISystemInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function process(): void
    {
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
                $this->entityManager->updateEntityComponents(
                    $entityId,
                    new MsTimeFromLastBehaviorActivation(
                        (int) floor(microtime(true) * 1000)
                    ),
                    new CurrentBehavior($triggeredBehavior)
                );

                $this->executeBehaviorEffects($entityToBeEvaluated, $triggeredBehavior);

                break;
            }
        }
    }

    /** @return TriggerValueEvaluatorWrapper[] */
    private function loadTriggerEvaluators(BehaviorPreset $behavior): array
    {
        $triggers = $behavior->getTriggers();
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
                BehaviorTriggerType::MS_TIME_FROM_LAST_BEHAVIOR_ACTIVATION =>
                    function (Entity $e) use ($triggerValue) {
                        /** @var ?MsTimeFromLastBehaviorActivation $component */
                        $component = $e->getComponent(MsTimeFromLastBehaviorActivation::class);
                        $lastActivationMsTime = $component?->getMsTime();
                        $currentMsTime = (int) floor(microtime(true) * 1000);
                        return $lastActivationMsTime === null
                            || ($currentMsTime - $lastActivationMsTime >= $triggerValue);
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
        $triggeredBehaviors = [];
        foreach ($behaviorCollection->getBehaviors() as $behavior) {
            /* TODO
                //check current entity behavior and check if any transition is possible based on its triggers.
                //if there is a transition possible, do transition to the state and
                //   stop the evaluation for the current entity.
             */

            //if not, check the triggers for $behavior
            $triggerValueEvaluators = $this->loadTriggerEvaluators($behavior);
            foreach ($triggerValueEvaluators as $evaluator) {
                if (!$evaluator->evaluateTrigger($entityToBeEvaluated)) {
                    continue 2;
                }
            }
            $t = 1;

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
                    break;
                }
            }
        }
    }
}
