<?php

declare(strict_types=1);

namespace App\System\Item;

class ItemManager
{

    /** @var ItemBlueprint[]  */
    private array $itemBlueprintById = [];
    private array $itemBlueprintByInternalName = [];


    public function getItemBlueprintByInternalName(string $internalName): ItemBlueprint
    {
        return $this->itemBlueprintByInternalName[$internalName] ??
            new ItemBlueprint(
                '87abfb0b-bf0b-48e5-bac7-5d37b8a05aa4',
                sprintf('<itemBlueprintNotFound: %s>', $internalName),
            );
    }

    public function loadItems(string $fileName): void
    {
        $rawItemData = file_get_contents($fileName);

        $itemData = json_decode($rawItemData);
        foreach ($itemData as $itemDatum) {
            $itemBlueprint = $this->loadItemBlueprintsByJson(json_encode($itemDatum));
            if ($itemBlueprint) {
                $this->itemBlueprintById[$itemBlueprint->getId()] = $itemBlueprint;
                $this->itemBlueprintByInternalName[$itemBlueprint->getInternalName()] = $itemBlueprint;
            }
        }
    }

    private function loadItemBlueprintsByJson(string $json): ?ItemBlueprint
    {
        $itemBlueprint = null;

        try {
            $data = json_decode($json, false);
            $itemBlueprint = new ItemBlueprint(
                $data->id,
                $data->internalName,
            );

            $itemBlueprint->setName($data->name ?? null);
            $itemBlueprint->setDescription($data->description ?? null);
            $itemBlueprint->setShortDescription($data->shortDescription ?? null);
            $itemBlueprint->setStackable($data->stackable ?? false);
            $itemBlueprint->setStackSize($data->stackSize ?? 1);

        } catch(\Throwable $e) {}

        return $itemBlueprint;
    }
}
