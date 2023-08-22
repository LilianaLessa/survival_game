<?php

declare(strict_types=1);

namespace App\System\Monster;

use App\Engine\Component\BehaviorCollection;
use App\System\Helpers\ConsoleColorPalette;
use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\PresetDataType;

class MonsterPreset extends AbstractPreset
{
    private float $baseMovementSpeed = 0;
    private float $baseAttackSpeed = 0;

    private int $totalHitPoints = 1;


    /** @var MonsterDropPreset[] */
    private array $dropCollection;

    private ConsoleColorPalette $defaultColor;

    public function __construct(
        string $name,
        private readonly ?string $symbol,
        //todo this is the quickest way to do this, as the data structure is the same,
        //     but it seems the layers are not being respected.
        private readonly BehaviorCollection $behaviorCollection,
    ) {
        parent::__construct(PresetDataType::MONSTER_PRESET, $name);

        $this->defaultColor = ConsoleColorPalette::default();
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getBehaviorCollection(): BehaviorCollection
    {
        return $this->behaviorCollection;
    }

    public function getBaseMovementSpeed(): float
    {
        return max($this->baseMovementSpeed ?? 0, 0);
    }

    public function setBaseMovementSpeed(float $baseMovementSpeed): self
    {
        $this->baseMovementSpeed = max($baseMovementSpeed, 0);
        return $this;
    }

    public function getBaseAttackSpeed(): float
    {
        return max($this->baseAttackSpeed ?? 0, 0);
    }

    public function setBaseAttackSpeed(float $baseAttackSpeed): self
    {
        $this->baseAttackSpeed = max($baseAttackSpeed, 0);
        return $this;
    }

    public function getTotalHitPoints(): int
    {
        return max($this->totalHitPoints ?? 1, 1);
    }

    public function setTotalHitPoints(int $totalHitPoints): self
    {
        $this->totalHitPoints = max(1, $totalHitPoints);
        return $this;
    }

    /** @return MonsterDropPreset[] */
    public function getDropCollection(): array
    {
        return $this->dropCollection;
    }

    public function setDropCollection(MonsterDropPreset ...$dropCollection): self
    {
        $this->dropCollection = $dropCollection;
        return $this;
    }

    public function getDefaultColor(): ConsoleColorPalette
    {
        return $this->defaultColor;
    }

    public function setDefaultColor(ConsoleColorPalette $defaultColor): self
    {
        $this->defaultColor = $defaultColor;
        return $this;
    }
}
