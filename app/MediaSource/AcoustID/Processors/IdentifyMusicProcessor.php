<?php

namespace App\MediaSource\AcoustID\Processors;

use App\Helpers\ShoutzorSetting;
use App\MediaSource\AcoustID\AcoustID;
use App\MediaSource\Base\Processors\Processor;
use App\MediaSource\ProcessorItem;
use App\Models\Album;
use App\Models\Artist;
use Closure;
use Exception;
use Illuminate\Support\Facades\Log;

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
        // Check if AcoustID is enabled
        if(ShoutzorSetting::getSetting('acoustid_enabled') === false) {
            return $next($item);
        }

        $upload = $item->getUpload();
        $media = $item->getMedia();

        $acoustID = new AcoustID();
        $info = $acoustID->getMediaInfo($upload->getFilePath());

        try {
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
            return $next($item);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            // Checks whether AcoustID validation is required
            if(ShoutzorSetting::getSetting('acoustid_required')) {
                throw $e;
            } else {
                return $next($item);
            }
        }
    }

}