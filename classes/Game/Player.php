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
    private string $job;
    private int $pvmax; //points de vie max
    private int $pv;
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

    public function set_job( string $job ):self{
        $this->job = $job;
        return $this;
    }
    public function get_job():string{
        return $this->job;
    }
    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        switch( $type ){




        }
        //define pv max



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
    public static function loadPlayer(string $file):Player{
        $data = file_get_contents($file);
        return unserialize($data); 

    }
    public function __toArray(): array
    {
        return [
            'uid' => isset($this->uid) ? $this->uid : "",
            'name' => isset($this->name) ? $this->name : "",
            'type' => isset($this->type) ? $this->type : "",
            'courage' => isset($this->courage) ? $this->courage : 0,
            'intelligence' => isset($this->intelligence) ? $this->intelligence : 0,
            'charisma' => isset($this->charisma) ? $this->charisma : 0,
            'dexterity' => isset($this->dexterity) ? $this->dexterity : 0,
            'strength' => isset($this->strength) ? $this->strength : 0,
            'equipment' => isset($this->equipment) ? $this->equipment : [],
            'specialFeatures' => isset($this->specialFeatures) ? $this->specialFeatures : "",
            'description' => isset($this->description) ? $this->description : "",
        ];
    }
}