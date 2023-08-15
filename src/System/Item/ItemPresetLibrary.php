<?php

declare(strict_types=1);

namespace App\System\Item;

//todo change it also to a preset library
use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\AbstractPresetLibrary;
use App\System\PresetLibrary\PresetDataType;

class ItemPresetLibrary extends AbstractPresetLibrary
{
    public function getPresetByName(string $name): ItemPreset
    {
        [ $preset ] = $this->getPresetByNameAndTypes(
            $name,
            PresetDataType::ITEM_PRESET
        );

        return $preset;
    }

    protected function createPreset(?PresetDataType $presetDataType, object $rawPreset): AbstractPreset
    {
        return (new ItemPreset($rawPreset->name))
        ->setDescription($rawPreset->description ?? null)
        ->setShortDescription($rawPreset->shortDescription ?? null)
        ->setStackable($rawPreset->stackable ?? false)
        ->setStackSize($rawPreset->stackSize ?? 1)
        ->setRarity(ItemRarity::tryFrom($rawPreset->rarity ?? ''))
        ->setItemPrice(new ItemPrice(
            $rawPreset->priceC ?? 0,
            $rawPreset->priceS ?? 0,
            $rawPreset->priceG ?? 0,
        ));
    }

    protected function getPresetTypesToLoad(): array
    {
        return [
            PresetDataType::ITEM_PRESET,
        ];
    }
}
