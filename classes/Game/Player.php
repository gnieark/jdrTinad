<?php

class Player
{
    private string $uid;
    private string $name;
    private string $type;
    private int $courage;
    private int $intelligence;
    private int $charisma;
    private int $dexterity;
    private int $strength;
    private array $equipment;
    private string $specialFeatures;
    private string $description;

    public function __construct() {
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getCourage(): int
    {
        return $this->courage;
    }

    public function setCourage(int $courage): self
    {
        $this->courage = $courage;
        return $this;
    }

    public function getIntelligence(): int
    {
        return $this->intelligence;
    }

    public function setIntelligence(int $intelligence): self
    {
        $this->intelligence = $intelligence;
        return $this;
    }

    public function getCharisma(): int
    {
        return $this->charisma;
    }

    public function setCharisma(int $charisma): self
    {
        $this->charisma = $charisma;
        return $this;
    }

    public function getDexterity(): int
    {
        return $this->dexterity;
    }

    public function setDexterity(int $dexterity): self
    {
        $this->dexterity = $dexterity;
        return $this;
    }

    public function getStrength(): int
    {
        return $this->strength;
    }

    public function setStrength(int $strength): self
    {
        $this->strength = $strength;
        return $this;
    }

    public function getEquipment(): array
    {
        return $this->equipment;
    }

    public function setEquipment(array $equipment): self
    {
        $this->equipment = $equipment;
        return $this;
    }

    public function getSpecialFeatures(): string
    {
        return $this->specialFeatures;
    }

    public function setSpecialFeatures(string $specialFeatures): self
    {
        $this->specialFeatures = $specialFeatures;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
    public function save ( string $file ):self
    {
        file_put_contents( $file, serialize($this) );
        return $this;
    }
}