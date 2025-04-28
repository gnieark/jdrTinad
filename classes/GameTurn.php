<?php

class PlayTurn{
    private string $mjGlobalPrompt;
    private array $playersDiscussions = array();

    public function set_mjGlobalPrompt( string $prompt ):PlayTurn {
        $this->mjGlobalPrompt = $prompt;
        return $this;
    }



}