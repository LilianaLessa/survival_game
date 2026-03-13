<?php

use App\Engine\Component\InGameName;
use App\Engine\Component\Player;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Item\ItemPresetLibrary;
use App\System\NPC\InteractionTriggerType;
use App\System\NPC\InteractionType;
use App\System\NPC\NpcDefinitionPreset;
use App\System\NPC\NpcManager;
use App\System\NPC\NpcPresetLibrary;
use App\System\PresetLibrary\PresetDataType;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\MenuItem\LineBreakItem;
use PhpSchool\CliMenu\MenuItem\SelectableItem;
use PhpSchool\CliMenu\MenuItem\StaticItem;

require_once(__DIR__ . '/../vendor/autoload.php');

class Util {

    /**
     * @var PresetDataType|mixed
     */
    public static ?NpcDefinitionPreset $selectedNpcPreset = null;
    public static ?Entity $selectedInteractor = null;
    public static ?InteractionTriggerType $selectedTriggerType = null;
    public static string $currentChoiceText;
    public static array $choiceToIdMap;
    private static NpcManager $npcManager;
    private static NpcPresetLibrary $npcPresetLibrary;
    private static ItemPresetLibrary $itemPresetLibrary;

    private static EntityManager $entityManager;

    public static array $interactors = [
        "AA",
        "BB"
    ];

    public static array $navigationStack = [
        "main"
    ];

    public static array $menus = [];

    /**
     * @var array<string, NpcDefinitionPreset> Registered NPCs by entity UUID
     */
    private static array $registeredByEntityId = [];

    /**
     * @var array<string, NpcDefinitionPreset> Registered NPCs by preset name
     */
    private static array $registeredByPresetName = [];

    static public function getNpcManager():  NpcManager
    {
        self::$npcManager = self::$npcManager ?? new NpcManager();
        return self::$npcManager;
    }

    static public function getNpcPresetLibrary():  NpcPresetLibrary
    {
        self::$npcPresetLibrary = self::$npcPresetLibrary ?? new NpcPresetLibrary();
        return self::$npcPresetLibrary;
    }

    static public function getItemPresetLibrary():  ItemPresetLibrary
    {
        self::$itemPresetLibrary = self::$itemPresetLibrary ?? new ItemPresetLibrary();
        return self::$itemPresetLibrary;
    }

    static public function getEntityManager():  EntityManager
    {
        self::$entityManager = self::$entityManager ?? new EntityManager();
        return self::$entityManager;
    }

    static public function addNpc(NpcDefinitionPreset $npc, Entity $entity): void
    {
        self::$registeredByEntityId[$entity->getId()] = $npc;
        self::$registeredByPresetName[$npc->getName()] = $npc;
    }

    static public function getNpcEntity(NpcDefinitionPreset $queryNpc): ?Entity
    {
        foreach (self::$registeredByEntityId as $entityId => $npc) {
            if ($npc->getName() === $queryNpc->getName()) {
                return self::$entityManager->getEntityById($entityId);
            }
        }

        return null;
    }
}

Util::getNpcPresetLibrary()->load("../data/Entity/NPC");
Util::getItemPresetLibrary()->load("../data/Item");

function changeMenu(string $nextMenu, CliMenu $menu) {
    Util::$navigationStack[] = $nextMenu;
    $menu->close();

    echo $menu->getSelectedItem()->getText() . "\n";
};


Util::$menus["main"] = [
    "menu" =>  (new CliMenuBuilder())
        ->disableDefaultItems()
        ->build(),
    "itemFetcher" => fn() => [
        "npcs" => ["Loaded NPCs", fn (CliMenu $menu) => changeMenu("npcs", $menu)]
    ],
];

Util::$menus["npcs"] = [
    "menu" =>  (new CliMenuBuilder())
        ->disableDefaultItems()
        ->build(),
    "itemFetcher" => function () {
        $npcs = Util::getNpcPresetLibrary()->getAllDefinitions();
        $items = [];
        foreach ($npcs as $npc) {
            $npcName  = $npc->getName();
            $items[$npcName] = [
                $npcName,
                function (CliMenu $menu) {
                    $selectedNpcText = $menu->getSelectedItem()->getText();
                    echo "SelectedNPC: " . $selectedNpcText;
                    $npcPresetLibrary = Util::getNpcPresetLibrary();
                    [$npcPreset] = $npcPresetLibrary->getPresetByNameAndTypes(
                        $selectedNpcText,
                        PresetDataType::NPC_DEFINITION
                    );
                    Util::$selectedNpcPreset = $npcPreset;

                    changeMenu("interactors", $menu);
                },
            ];
        }

        return $items;
    }
];

Util::$menus["interactors"] = [
    "menu" =>  (new CliMenuBuilder())
        ->disableDefaultItems()
        ->build(),
    "itemFetcher" => function () {
        $entityManager = Util::getEntityManager();
        $players = $entityManager->getEntitiesWithComponents(Player::class, InGameName::class);
        $items = [];

        /** @var InGameName $inGameName */
        foreach ($players as $entityUuid => [,$inGameName]) {
            $items[$inGameName->getInGameName()] = [
                $inGameName->getInGameName(),
                function (CliMenu $menu) use ($entityUuid, $entityManager) {
                    Util::$selectedInteractor = $entityManager->getEntityById($entityUuid);
                    changeMenu("triggers", $menu);
                },
            ];
        }

        return $items;
    }
];

Util::$menus["triggers"] = [
    "menu" =>  (new CliMenuBuilder())
        ->disableDefaultItems()
        ->build(),
    "itemFetcher" => function () {

        $triggers = Util::$selectedNpcPreset->getTriggers();
        $items = [];
        foreach ($triggers as $trigger) {
            $items[$trigger->getType()->name] = [
                $trigger->getType()->value,
                function (CliMenu $menu) {
                    $selectedTriggerText = $menu->getSelectedItem()->getText();
                    Util::$selectedTriggerType = InteractionTriggerType::from($selectedTriggerText);

                    changeMenu("npcManagerMenuEntryPoint", $menu);
                }
            ];
        }

        return $items;
    }
];

function registerNpc(NpcDefinitionPreset $npcPreset): void
{
    $npcManager = Util::getNpcManager();
    if (!$npcManager->exists($npcPreset)) {
        $entity = Util::getEntityManager()->createEntity();
        Util::addNpc($npcPreset, $entity);
        $npcManager->register($npcPreset, $entity);
        echo "Npc Registered\n";
    }
}

Util::$menus["npcManagerMenuEntryPoint"] = [
    "menu" =>  (new CliMenuBuilder())
        ->disableDefaultItems()
        ->build(),
    "itemFetcher" => function () {
        $npcPreset = Util::$selectedNpcPreset;
        registerNpc($npcPreset);

        $npcEntity = Util::getNpcEntity($npcPreset);
        if (!$npcEntity) {
            throw new Exception("NPC Entity not found");
        }

        $npcManager = Util::getNpcManager();

        $result = $npcManager->interact(
            Util::$selectedInteractor,
            $npcEntity,
            InteractionType::START_INTERACTION,
            Util::$selectedTriggerType
        );

        //todo create it based on concrete return type.
        $items["npcText"] = [$result->text, function (CliMenu $menu) {}];
        $items["separator"] = ["-------", function (CliMenu $menu) {}];

        Util::$choiceToIdMap = [];
        foreach ($result->choices as $choiceId => $choiceText) {
            Util::$choiceToIdMap[$choiceText] = $choiceId;
            $items[$choiceId] = [
                $choiceText,
                function (CliMenu $menu) {
                    echo $menu->getSelectedItem()->getText() . "\n";

                    Util::$currentChoiceText = $menu->getSelectedItem()->getText();
                    changeMenu("npcManagerMenuDialog", $menu);
                }
            ];
        }

        return $items;
    }
];

Util::$menus["npcManagerMenuDialog"] = [
    "menu" =>  (new CliMenuBuilder())
        ->disableDefaultItems()
        ->build(),
    "itemFetcher" => function () {
        $npcPreset = Util::$selectedNpcPreset;

        $npcEntity = Util::getNpcEntity($npcPreset);
        if (!$npcEntity) {
            throw new Exception("NPC Entity not found");
        }

        $npcManager = Util::getNpcManager();
        $currentChoiceId = Util::$choiceToIdMap[Util::$currentChoiceText] ?? null;

        $items = [];
        if ($currentChoiceId) {
            $result = $npcManager->interact(
                Util::$selectedInteractor,
                $npcEntity,
                InteractionType::DIALOG_CHOICE,
                Util::$selectedTriggerType,
                [
                    "choiceId" => $currentChoiceId
                ]
            );

            //todo create it based on concrete return type.
            $items["npcText"] = [$result->text, function (CliMenu $menu) {}];
            $items["separator"] = ["-------", function (CliMenu $menu) {}];

            Util::$choiceToIdMap = [];
            foreach ($result->choices as $choiceId => $choiceText) {
                Util::$choiceToIdMap[$choiceText] = $choiceId;
                $items[$choiceId] = [
                    $choiceText,
                    function (CliMenu $menu) {
                        echo $menu->getSelectedItem()->getText() . "\n";

                        Util::$currentChoiceText = $menu->getSelectedItem()->getText();
                        changeMenu("npcManagerMenuDialog", $menu);
                    }
                ];
            }
        }


        return $items;
    }
];


//todo here create interactors with some stuff, like inventory or other state contexts

$entityManager = Util::getEntityManager();
$itemPresetLibrary = Util::getItemPresetLibrary();
foreach (Util::$interactors as $interactor) {
    $inventory = new \App\Engine\Component\Item\Inventory();

    $inventory->addItem(
        $entityManager,
        $entityManager->createEntity(
        new \App\Engine\Component\Item\ItemOnInventory(
            $itemPresetLibrary->getPresetByName("copperOre"),
            3,
        ))
    );

    $inventory->addItem(
        $entityManager,
        $entityManager->createEntity(
            new \App\Engine\Component\Item\ItemOnInventory(
                $itemPresetLibrary->getPresetByName("ironOre"),
                4,
            ))
    );

    $entityManager->createEntity(
        new \App\Engine\Component\Player(),
        new InGameName($interactor),
        $inventory,
    );
}


do {
    !$shutdown = false;
    $currentMenuId = end(Util::$navigationStack);

    /** @var CliMenu $currentMenu */
    [
        "menu" => $currentMenu,
        "itemFetcher" => $itemFetcher,
    ] = Util::$menus[$currentMenuId] ?? Util::$menus["main"];

    $currentMenuItems = $itemFetcher(Util::$navigationStack);

    //remove all items from menu.
    $currentItems = $currentMenu->getItems();
    foreach ($currentItems as $item) {
        $currentMenu->removeItem($item);
    }

    //interactor and selected npc.
    if ($currentMenuId === "triggers") {
        $currentMenu->addItem(new StaticItem(sprintf(
                "Selected: %s -> %s",
                Util::$selectedNpcPreset->getName(),
                Util::$selectedInteractor->getComponent(InGameName::class)->getInGameName(),
            )
        ));
        $currentMenu->addItem(new LineBreakItem('-'));
    }

    if ($currentMenuId === "npcManagerMenu") {
        $currentMenu->addItem(new StaticItem(sprintf(
                "Selected: %s -> %s -> %s",
                Util::$selectedNpcPreset->getName(),
                Util::$selectedInteractor->getComponent(InGameName::class)->getInGameName(),
                Util::$selectedTriggerType->value,
            )
        ));
        $currentMenu->addItem(new LineBreakItem('-'));
    }

    foreach ($currentMenuItems as $itemId => [$itemText, $itemHandler]) {
        $currentMenu->addItem(new SelectableItem($itemText, $itemHandler));
    }

    //navigation items.
    $currentMenu->addItem(new LineBreakItem('-'));
    if (count(Util::$navigationStack) > 1) {
        $currentMenu->addItem(new SelectableItem(
            "Go Back", function (CliMenu $menu) {
            array_pop(Util::$navigationStack);
            $menu->close();
        }
        ));
    }
    if (Util::$selectedNpcPreset && Util::$selectedInteractor && Util::$selectedTriggerType) {
        $currentMenu->addItem(new SelectableItem(
            "End Interaction", function (CliMenu $menu) use (&$shutdown) {
            $npcPreset = Util::$selectedNpcPreset;

            $npcEntity = Util::getNpcEntity($npcPreset);
            if (!$npcEntity) {
                throw new Exception("NPC Entity not found");
            }
            Util::getNpcManager()->interact(
                Util::$selectedInteractor,
                $npcEntity,
                InteractionType::END_INTERACTION,
                Util::$selectedTriggerType,
            );

            Util::$selectedNpcPreset = Util::$selectedInteractor = Util::$selectedTriggerType = null;

            Util::$navigationStack = [
                "main"
            ];
            $menu->close();
            Util::$menus["main"]["menu"]->open();
        }
        ));
    }
    $currentMenu->addItem(new SelectableItem(
        "Exit", function (CliMenu $menu) use (&$shutdown) {
            $shutdown = true;
            $menu->close();
        }
    ));
    $currentMenu->setTitle(implode(" > ", Util::$navigationStack));
    $currentMenu->open();
} while (!$shutdown);



