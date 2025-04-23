<?php
class Group {
    const TABLE = "groups";
    private string $name;
    private int $id;


    
    static public function get_table_name(): string {
        return self::TABLE;
    }

    public function __construct( int $id=null, string $name=null){
        if(!is_null($id)){ $this->set_id($id); }
        if(!is_null($name)){ $this->set_name($name); }
    }

    public function set_name( string $name ) : Group { $this->name = $name; return $this; }
    public function get_name():string { return $this->name; }

    public function set_id(int $id) :Group { $this->id = $id; return $this; }
    public function get_id():int { return $this->id; }
    

}