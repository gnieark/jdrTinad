<?php

class Player
{
    private string $uid;
    private string $name;
    private int $courage;
    private int $intelligence;
    private int $charisma;
    private int $dexterity;
    private int $strength;
    private string $job;
    private int $pv;
    private int $fortune = 0;
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
    public function setFortune(int $fortune):self{
        $this->fortune = $fortune;
        return $this;
    }
    public function getFortune():int{
        return $this->fortune;
    }
    public function applyDeltaFortune(int $delta):self{
        $this->fortune = $this->fortune + $delta;
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

    public function setJob( string $job ):self{
        $this->job = $job;
        return $this;
    }
    public function getJob():string{
        return $this->job;
    }
    public function setPv(int $pv):self{
        $this->pv = $pv;
        return $this;
    }
    public function getPv():int{
        return $this->pv;
    }
    public function applyDeltaPv(int $delta):self{
        $this->pv = $this->pv + $delta;
        return $this;
    }


    public function getMaxPv():int{
        return static::$maxPV;
    }

    public function getOrigine(): string {
        return static::$origine_title ?? 'inconnue';
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
    public function removeEquipment(string $piece): self
    {
        $this->equipment = array_values(
            array_filter($this->equipment, fn($item) => $item !== $piece)
        );
        return $this;
    }
    public function addEquipment(string $piece):self
    {
        $this->equipment[] = $piece;
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
    public function get_instructions_generation_competances(): string {
        $instructions = "";
    
        if (isset(static::$minFO)) {
            $instructions .= " Force minimale: " . static::$minFO . ".";
        }
        if (isset(static::$maxFO)) {
            $instructions .= " Force maximale: " . static::$maxFO . ".";
        }
        if (isset(static::$minCOU)) {
            $instructions .= " Courage minimal: " . static::$minCOU . ".";
        }
        if (isset(static::$maxCOU)) {
            $instructions .= " Courage maximal: " . static::$maxCOU . ".";
        }
        if (isset(static::$minINT)) {
            $instructions .= " Intelligence minimale: " . static::$minINT . ".";
        }
        if (isset(static::$maxINT)) {
            $instructions .= " Intelligence maximale: " . static::$maxINT . ".";
        }
        if (isset(static::$minAD)) {
            $instructions .= " Adresse minimale: " . static::$minAD . ".";
        }
        if (isset(static::$maxAD)) {
            $instructions .= " Adresse maximale: " . static::$maxAD . ".";
        }
        if (isset(static::$minCHA)) {
            $instructions .= " Charisme minimal: " . static::$minCHA . ".";
        }
        if (isset(static::$maxCHA)) {
            $instructions .= " Charisme maximal: " . static::$maxCHA . ".";
        }
    
        return trim($instructions);
    }
    public function __toArray(): array
    {
        return [
            'uid'               => isset($this->uid) ? $this->uid : "",
            'name'              => isset($this->name) ? $this->name : "",
            'job'               => $this->getJob(),
            'origine'           => $this->getOrigine(),
            'lifePoints'        => $this->getPv(),
            'lifePointsMax'     => $this->getMaxPv(),
            'fortune'           => $this->getFortune(),
            'courage'           => isset($this->courage) ? $this->courage : 0,
            'intelligence'      => isset($this->intelligence) ? $this->intelligence : 0,
            'charisma'          => isset($this->charisma) ? $this->charisma : 0,
            'dexterity'         => isset($this->dexterity) ? $this->dexterity : 0,
            'strength'          => isset($this->strength) ? $this->strength : 0,
            'equipment'         => isset($this->equipment) ? $this->equipment : [],
            'specialFeatures'   => isset($this->specialFeatures) ? $this->specialFeatures : "",
            'description'       => isset($this->description) ? $this->description : "",
        ];
    }
}