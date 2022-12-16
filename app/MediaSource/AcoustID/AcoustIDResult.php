<?php

namespace App\MediaSource\AcoustID;

use App\MediaSource\AcoustID\Result\AcoustIDAlbum;
use App\MediaSource\AcoustID\Result\AcoustIDArtist;

class AcoustIDResult
{
    private array $artists = [];
    private array $albums = [];

    public function __construct(
        private readonly string $title
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getArtists(): array
    {
        return $this->artists;
    }

    /**
     * @return array
     */
    public function getAlbums(): array
    {
        return $this->albums;
    }

    public function addArtist(AcoustIDArtist $artist): void
    {
        $this->artists[] = $artist;
    }

    public function addAlbum(AcoustIDAlbum $album): void
    {
        $this->albums[] = $album;
    }

}