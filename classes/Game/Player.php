<?php
use League\CommonMark\CommonMarkConverter;

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
    private int $xp = 0;
    private string $magicSpecialty = "";
    private int $ea = 0;
    private int $eaMax = 0;

    public function __construct() {}

    // === UID ===
    public function getUid(): string { return $this->uid; }
    public function setUid(string $uid): self { $this->uid = $uid; return $this; }

    // === Name ===
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    // === Courage ===
    public function getCourage(): int { return $this->courage; }
    public function setCourage(int $courage): self { $this->courage = $courage; return $this; }

    // === Intelligence ===
    public function getIntelligence(): int { return $this->intelligence; }
    public function setIntelligence(int $intelligence): self { $this->intelligence = $intelligence; return $this; }

    // === Charisma ===
    public function getCharisma(): int { return $this->charisma; }
    public function setCharisma(int $charisma): self { $this->charisma = $charisma; return $this; }

    // === Dexterity ===
    public function getDexterity(): int { return $this->dexterity; }
    public function setDexterity(int $dexterity): self { $this->dexterity = $dexterity; return $this; }

    // === Strength ===
    public function getStrength(): int { return $this->strength; }
    public function setStrength(int $strength): self { $this->strength = $strength; return $this; }

    // === Job ===
    public function getJob(): string { return $this->job; }
    public function setJob(string $job): self {
        if(($job == "mage") || ($job == "sorcer")){
            $this->setEaMax(30);
            $this->setEA(30);
        }
        $this->job = $job; 
        return $this; 
    }

    // === Pv ===
    public function getPv(): int { return $this->pv; }
    public function setPv(int $pv): self {
        $this->pv = $pv;
        if ($this->pv > static::$maxPV) $this->pv = static::$maxPV;
        return $this;
    }
    public function applyDeltaPv(int $delta): self {
        $this->pv += $delta;
        if ($this->pv > static::$maxPV) $this->pv = static::$maxPV;
        return $this;
    }

    // === Fortune ===
    public function getFortune(): int { return $this->fortune; }
    public function setFortune(int $fortune): self { $this->fortune = $fortune; return $this; }
    public function applyDeltaFortune(int $delta): self {
        $this->fortune += $delta;
        return $this;
    }

    // === Equipment ===
    public function getEquipment(): array { return $this->equipment; }
    public function setEquipment(array $equipment): self { $this->equipment = $equipment; return $this; }
    public function addEquipment(string $piece): self { $this->equipment[] = $piece; return $this; }
    public function removeEquipment(string $piece): self {
        $this->equipment = array_values(array_filter($this->equipment, fn($item) => $item !== $piece));
        return $this;
    }

    // === Special Features ===
    public function getSpecialFeatures(): string { return $this->specialFeatures; }
    public function setSpecialFeatures(string $specialFeatures): self {
        $this->specialFeatures = $specialFeatures; return $this;
    }

    // === Description ===
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self {
        $this->description = $description; return $this;
    }

    // === XP ===
    public function getXp(): int { return $this->xp; }
    public function setXp(int $xp): self { $this->xp = $xp; return $this; }
    public function addXP(int $xpToAdd): self { $this->xp += $xpToAdd; return $this; }

    // === Magic Speciality ===
    public function getMagicSpeciality(): string { return $this->magicSpecialty; }
    public function setMagicSpeciality(string $magicSpeciality): self {
        if (!isset($this->job)) throw new Exception("job must be setted before magicSpeciality.");

        $commonMagic = ["magie de l'Air", "magie Eau et Glace", "magie thermodynamique"];
        $mageMagic = ["mage de la Terre", "Mage de Combat", "Mage du Feu", "Mage Invocateur", "Nécromancien", "mage Métamorphe"];
        $sorcererMagic = ["sorcier noir de Tzinntch"];

        switch ($this->job) {
            case "mage":
            case "sorcier":
                $availableSpecialities = array_merge($commonMagic, $mageMagic);
                break;
            default:
                throw new Exception("job must be one of 'mage' or 'sorcier' to set magicSpeciality");
        }

        if (!in_array($magicSpeciality, $availableSpecialities)) {
            throw new Exception("With job " . $this->job . " magic speciality must be one of " . implode(",", $availableSpecialities));
        }
        $this->magicSpecialty = $magicSpeciality;
        return $this;
    }

    // === Energie Astrale ===
    public function getEa(): int { return $this->ea; }
    public function setEA(int $ea): self {
        $this->ea = $ea;
        if ($this->ea > $this->eaMax) $this->ea = $this->eaMax;
        return $this;
    }
    public function applyDeltaEa(int $delta): self {
        $this->ea += $delta;
        if ($this->ea > $this->eaMax) $this->ea = $this->eaMax;
        return $this;
    }
    public function setEaMax(int $eaMax): self { $this->eaMax = $eaMax; return $this; }
    public function getEaMax():int{return $this->eaMax; }
    // === Autres méthodes ===
    public function getLevel(): int {
        $xp = $this->xp;
        $levels = [1 => 0, 2 => 100, 3 => 300, 4 => 600, 5 => 1000, 6 => 1500, 7 => 2100, 8 => 2800, 9 => 3600, 10 => 4500,
            11 => 5500, 12 => 6600, 13 => 7800, 14 => 9100, 15 => 10500, 16 => 12000, 17 => 13600, 18 => 15300, 19 => 17100,
            20 => 19000, 21 => 21000, 22 => 24000, 23 => 29000, 24 => 35000, 25 => 45000, 26 => 60000];
        $level = 1;
        foreach ($levels as $lvl => $threshold) {
            if ($xp >= $threshold) $level = $lvl;
            else break;
        }
        return $level;
    }

    public function getMaxPv(): int { return static::$maxPV; }
    public function getOrigine(): string { return static::$origine_title ?? 'inconnue'; }

    public function save(string $file): self {
        file_put_contents($file, serialize($this));
        return $this;
    }

    public function get_instructions_generation_competances(): string {
        $instructions = "";
        foreach (["FO", "COU", "INT", "AD", "CHA"] as $attr) {
            if (isset(static::${"min$attr"})) $instructions .= " $attr minimale: " . static::${"min$attr"} . ".";
            if (isset(static::${"max$attr"})) $instructions .= " $attr maximale: " . static::${"max$attr"} . ".";
        }
        return trim($instructions);
    }

    public function __toArray(): array {
        $arr = [
            'uid' => $this->uid ?? "",
            'name' => $this->name ?? "",
            'job' => $this->getJob(),
            'origine' => $this->getOrigine(),
            'xp' => $this->xp,
            'level' => $this->getLevel(),
            'lifePoints' => $this->getPv(),
            'lifePointsMax' => $this->getMaxPv(),
            'fortune' => $this->getFortune(),
            'courage' => $this->courage ?? 0,
            'intelligence' => $this->intelligence ?? 0,
            'charisma' => $this->charisma ?? 0,
            'dexterity' => $this->dexterity ?? 0,
            'strength' => $this->strength ?? 0,
            'equipment' => $this->equipment ?? [],
            'specialFeatures' => $this->specialFeatures ?? "",
            'description' => $this->description ?? "",
        ];

        if(($this->job == "mage") || ($this->job == "sorcer")){
            $arr["specialiteMagie"]     = $this->getMagicSpeciality();
            $arr["energieAstrale"]      = $this->getEa();
            $arr["energieAstraleMax"]   = $this->getEaMax();
        }
        return $arr;
    }

    // === Méthodes statiques ===
    public static function loadPlayer(string $file): Player {
        return unserialize(file_get_contents($file));
    }

    public static function get_all_origine_desc(bool $convert_as_html = false): array {
        $descriptions = [];
        foreach (glob(__DIR__ . "/Player_*.php") as $filename) {
            require_once $filename;
            $basename = basename($filename, ".php");
            if (class_exists($basename) && is_subclass_of($basename, Player::class)) {
                $origine = $basename::getOrigineStatic();
                $descriptions[$origine] = $convert_as_html
                    ? (string)(new CommonMarkConverter())->convertToHtml($basename::get_origine_desc())
                    : $basename::get_origine_desc();
            }
        }
        return $descriptions;
    }

    public static function getOrigineStatic(): string {
        return static::$origine_title ?? 'inconnue';
    }
}