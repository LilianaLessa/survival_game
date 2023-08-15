<?php

declare(strict_types=1);

namespace App\System\PresetLibrary;

class AbstractPreset
{
    public function __construct(protected readonly PresetDataType $presetDataType, protected readonly string $name)
    {
    }

    public function getPresetDataType(): PresetDataType
    {
        return $this->presetDataType;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
