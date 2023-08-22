<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\System\Monster\Spawner\MonsterSpawnerPreset;

class ParentSpawner implements ComponentInterface
{
    public function __construct(private readonly MonsterSpawnerPreset $parentSpawner)
    {
    }

    public function getParentSpawner(): MonsterSpawnerPreset
    {
        return $this->parentSpawner;
    }
}
