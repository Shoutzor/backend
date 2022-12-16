<?php

namespace App\MediaSource\AcoustID\Result;

class AcoustIDArtist {

    private $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

}