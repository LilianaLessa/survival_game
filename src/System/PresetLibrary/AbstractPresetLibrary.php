<?php

declare(strict_types=1);

namespace App\System\PresetLibrary;

abstract class AbstractPresetLibrary
{
    /**
     * @var AbstractPreset[][]
     */
    private array $presetsByTypeAndName = [];

    public function load(string $presetDataDirectory): void
    {
        $presetTypesToLoad = $this->getPresetTypesToLoad();

        $rawPresetsData = $this->loadRawPresetData(
            $presetDataDirectory,
            ...$presetTypesToLoad,
        );

        $rawPresetsData = array_combine(
            array_map(fn (PresetDataType $t) => $t->value, $presetTypesToLoad),
            $rawPresetsData
        );

        foreach ($rawPresetsData as $presetType => $rawPresets) {
            foreach ($rawPresets as $rawPreset) {
                if ($rawPreset->name ?? null) {
                    $this->presetsByTypeAndName[$presetType][$rawPreset->name] =
                        $this->createPreset(PresetDataType::tryFrom($presetType), $rawPreset);
                }
            }
        }
    }

    /**
     * @return ?PresetDataType[]
     */
    public function getPresetByNameAndTypes(string $presetName, PresetDataType ...$presetDataTypes): array
    {
        $results = [];

        foreach ($presetDataTypes as $presetDataType) {
            $results[] = $this->presetsByTypeAndName[$presetDataType->value][$presetName] ?? null;
        }

        return $results;
    }

    private function loadRawPresetData(string $presetFilesDirectory, PresetDataType ...$presetDataTypes): array
    {
        $rawPresetData = [];

        $presetFilesDirectoryIterator = new \DirectoryIterator($presetFilesDirectory);

        foreach ($presetFilesDirectoryIterator as $fileInfo) {
            if ($fileInfo->isFile() && $fileInfo->getExtension() === 'json') {
                $jsonData = file_get_contents($fileInfo->getRealPath());
                $rawData = json_decode($jsonData, false);
                $rawObjectCollection = is_object($rawData) ? [$rawData] : $rawData;
                foreach ($rawObjectCollection as $rawObject) {
                    $dataType = PresetDataType::tryFrom($rawObject?->dataType ?? '');
                    if (in_array($dataType, $presetDataTypes)) {
                        $rawPresetData[$dataType->value][] = $rawObject;
                    }
                }
            }
        }

        return array_values($rawPresetData);
    }

    abstract protected  function createPreset(?PresetDataType $presetDataType, mixed $rawPreset): AbstractPreset;

    /** @return PresetDataType[] */
    abstract protected function getPresetTypesToLoad(): array;
}
