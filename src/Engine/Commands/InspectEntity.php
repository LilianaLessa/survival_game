<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\EntityManager;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\Kernel;

class InspectEntity implements InvokableCommandInterface
{
    public function __construct(
        private readonly string $entityId,
        private readonly string $componentClass,

    ) {
    }

    public function __invoke(PlayerCommandQueue $playerCommandQueue)
    {
        /** @var EntityManager $entityManager */
        $entityManager = Kernel::getContainer()->get(EntityManager::class);

        $targetEntity = $entityManager->getEntityById($this->entityId);
        if ($targetEntity) {
            $uiMessage = sprintf("Inspecting entity %s, %s\n\n", $this->entityId, $this ->componentClass);

            $uiMessage .= print_r($targetEntity->getComponent($this->componentClass), true);

            $uiMessage .= "\n";

            Dispatcher::getInstance()->dispatch(new UiMessageEvent($uiMessage, $playerCommandQueue));
        }
    }
}
