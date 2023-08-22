<?php

declare(strict_types=1);

namespace App\Engine\Component;

class AggroList implements ComponentInterface
{
    private array $aggroByEntityId = [];


    public function addAggro(string $entityId, float $aggroValue): void
    {
        $this->aggroByEntityId[$entityId] = $this->aggroByEntityId[$entityId] ?? 0;

        $this->aggroByEntityId[$entityId] += max(0, $aggroValue);

        asort($this->aggroByEntityId, SORT_NUMERIC);
    }

    public function cleanAggro(string $entityId): void
    {
        unset($this->aggroByEntityId[$entityId]);
    }

    public function clean(): void
    {
        $this->aggroByEntityId = [];
    }

    public function getAggroByEntityId(): array
    {
        return $this->aggroByEntityId;
    }

    public function getAggroListTop(): ?string
    {
        $last = end($this->aggroByEntityId);

        return $last === false ? null : $last;
    }
}
