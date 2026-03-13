<?php

namespace App\System\NPC;

class DialogNode
{
    /** @var DialogNode[]  */
    private array $choices = [];

    private string $text;

    public function __construct(
        public readonly ?string $id,
    )
    {
    }

    public function addChoice(DialogNodeChoice $choice): void
    {
        $this->choices[$choice->id] = $choice;
    }

    /**
     * @return DialogNodeChoice[]
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    public function getText(): ?string
    {
        return $this->text ?? null;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }
}