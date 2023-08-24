<?php

declare(strict_types=1);

namespace App\System\Player;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\PresetDataType;

class PlayerPreset extends AbstractPreset
{
    private int $initialViewportWidth;
    private int $initialViewportHeight;

    private string $defaultSymbol;

    private int $totalHitPoints = 10;

    private int $baseMovementSpeed = 10;

    public function __construct(string $name)
    {
        parent::__construct(
            PresetDataType::PLAYER_PRESET,
            $name,
        );
    }

    public function getInitialViewportWidth(): int
    {
        return $this->initialViewportWidth;
    }

    public function setInitialViewportWidth(int $initialViewportWidth): self
    {
        $this->initialViewportWidth = $initialViewportWidth;
        return $this;
    }

    public function getInitialViewportHeight(): int
    {
        return $this->initialViewportHeight;
    }

    public function setInitialViewportHeight(int $initialViewportHeight): self
    {
        $this->initialViewportHeight = $initialViewportHeight;
        return $this;
    }

    public function getDefaultSymbol(): string
    {
        return $this->defaultSymbol;
    }

    public function setDefaultSymbol(string $defaultSymbol): self
    {
        $this->defaultSymbol = $defaultSymbol;
        return $this;
    }

    public function getTotalHitPoints(): int
    {
        return $this->totalHitPoints;
    }

    public function setTotalHitPoints(int $totalHitPoints): self
    {
        $this->totalHitPoints = $totalHitPoints;
        return $this;
    }

    public function getBaseMovementSpeed(): int
    {
        return $this->baseMovementSpeed;
    }

    public function setBaseMovementSpeed(int $baseMovementSpeed): self
    {
        $this->baseMovementSpeed = $baseMovementSpeed;
        return $this;
    }
}
