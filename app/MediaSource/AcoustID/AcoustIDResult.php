<?php

namespace App\MediaSource\AcoustID;

class AcoustIDResult
{
    private array $artists = [];
    private array $albums = [];

    public function __construct(
        private readonly string $title
    ) {}

    public function getTitle() {
        return $this->title;
    }

    public function getArtists() {
        return $this->artists;
    }

    public function getAlbums() {
        return $this->albums;
    }

    public function addArtist(string $name) {
        $this->artists[] = $name;
    }

    public function addAlbum(string $title) {
        $this->albums[] = $title;
    }

}