<?php

declare(strict_types=1);

namespace App\System\Monster;

class MonsterBlueprint
{
    private ?string $name = null;
    private ?string $description = null;
    private ?int $level = null;
    private ?int $totalHitPoints = null;
    private ?string $symbol = null;
    private array $dropCollection = [];
    private ?array $spawnArea = null;
    private ?int $maxInMap = null;
    private ?float $spawnChance = null;
    private ?array $spawnBiomes = null;
    private ?int $sightRadius = null;
    private ?int $hearRadius = null;
    private ?string $behavior = null;
    private array $elements = [];

    private ?MonsterType $type = null;
    private ?MonsterRace $race = null;
    private ?MonsterSize $size = null;
    private ?float $movementSpeed = null;
    private ?float $attackSpeed = null;


    public function __construct(
        private readonly string $id,
        private readonly string $internalName,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getInternalName(): string
    {
        return $this->internalName;
    }
}
