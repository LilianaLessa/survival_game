<?php

declare(strict_types=1);

namespace App\System;

trait PresetDataLoaderTrait
{
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
}
