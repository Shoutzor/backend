<?php

namespace App\MediaSource\AcoustID\Result;

class AcoustIDAlbum {

    private $name;
    private $image = null;

    public function __construct($name) {
        $this->name = $name;
    }

    public function setImage($image) {
        $this->image = $image;
    }

    public function getName() {
        return $this->name;
    }

    public function getImage() {
        return $this->image;
    }

}