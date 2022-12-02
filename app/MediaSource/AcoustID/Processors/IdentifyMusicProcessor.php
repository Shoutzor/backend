<?php

namespace App\MediaSource\AcoustID\Processors;

use App\MediaSource\AcoustID\AcoustID;
use App\MediaSource\Base\Processors\Processor;
use App\MediaSource\ProcessorItem;
use App\Models\Album;
use App\Models\Artist;
use Closure;

class IdentifyMusicProcessor extends Processor
{
    /**
     * Use Chromaprint to generate a fingerprint of the audio.
     * Then we use the AcoustID API to update the file information
     *
     * @param ProcessorItem $item
     * @param Closure $next
     * @return mixed|void
     */
    public function handle(ProcessorItem $item, Closure $next) {
        $upload = $item->getUpload();
        $media = $item->getMedia();

        $acoustID = new AcoustID();
        $info = $acoustID->getMediaInfo($upload->getFilePath());

        // Audio Fingerprint lookup returned no results, Skip.
        if($info === null) {
            return $next($item);
        }

        $artists = [];
        foreach($info->getArtists() as $artist) {
            $artists[] = Artist::firstOrCreate([
                'name' => $artist
            ], [
                'name' => $artist
            ]);
        }

        $albums = [];
        foreach($info->getAlbums() as $album) {
            $albums[] = Album::firstOrCreate([
                'title' => $album
            ], [
                'title' => $album
            ]);
        }

        // Update the media object with the obtained information
        $media->title = $info->getTitle();
        $media->artists()->saveMany($artists);
        $media->albums()->saveMany($albums);

        // File exists, continue processing
        $next($item);
    }

}