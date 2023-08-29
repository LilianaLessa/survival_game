<?php

declare(strict_types=1);

namespace App\System\PresetLibrary;

use App\System\Kernel;

class PresetLibrariesLoader extends AbstractPresetLibrary
{
    protected function createPreset(?PresetDataType $presetDataType, object $rawPreset): AbstractPreset
    {
        static $libraries = null;
        $libraries ??= Kernel::getAllRegisteredConcreteInstances(AbstractPresetLibrary::class);

        $dir = __DIR__;
        $dir = explode(DIRECTORY_SEPARATOR, $dir);
        do {
            $srcDir = array_pop($dir);
        } while ($srcDir !== 'src');
        $baseDir = implode(DIRECTORY_SEPARATOR, $dir);

        /**
         * @var string $libraryServiceId
         * @var AbstractPresetLibrary $library
         */
        foreach ($libraries as $libraryServiceId => $library) {
            $explodedId = explode("\\", $libraryServiceId);
            $libraryName = end($explodedId);
            if ($libraryName === $rawPreset->name) {
                echo sprintf("Loading %s...\n", $rawPreset->name);
                $library->load(
                    sprintf(
                        '%s%s%s',
                        $baseDir,
                        DIRECTORY_SEPARATOR,
                        $rawPreset->folder,
                    )
                );
                break;
            }
        }

        return new class($rawPreset->name) extends AbstractPreset {
            public function __construct(string $name)
            {
                parent::__construct(
                    PresetDataType::LIBRARY_CONFIG,
                    $name
                );
            }
        };
    }

    protected function getPresetTypesToLoad(): array
    {
        return [
            PresetDataType::LIBRARY_CONFIG
        ];
    }
}
