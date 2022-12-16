<?php

namespace App\MediaSource\AcoustID\Processors;

use App\Helpers\ShoutzorSetting;
use App\MediaSource\AcoustID\AcoustID;
use App\MediaSource\AcoustID\LastFM;
use App\MediaSource\Base\Processors\Processor;
use App\MediaSource\Base\Processors\ProcessorError;
use App\MediaSource\ProcessorItem;
use App\Models\Album;
use App\Models\Artist;
use Closure;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IdentifyMusicProcessor extends Processor
{
    /**
     * Use Chromaprint to generate a fingerprint of the audio.
     * Then we use the AcoustID API to update the file information
     *
     * @TODO refactor this class
     *
     * @param ProcessorItem $item
     * @param Closure $next
     * @return mixed|void
     */
    public function handle(ProcessorItem $item, Closure $next) {
        // Check if AcoustID is enabled
        if(ShoutzorSetting::getSetting('acoustid_enabled') === false) {
            if(ShoutzorSetting::getSetting('acoustid_required') === false) {
                return $next($item);
            }

            return new ProcessorError("AcoustID is required, but is disabled");
        }

        $upload = $item->getUpload();
        $media = $item->getMedia();

        $acoustID = new AcoustID();
        $lastFM = new LastFM();
        $lastFMEnabled = ShoutzorSetting::getSetting('acoustid_use_lastfm');

        $info = $acoustID->getMediaInfo($upload->getFilePath());

        try {
            // Audio Fingerprint lookup returned no results
            if($info === null) {
                throw new Exception("AcoustID returned no results");
            }

            $artists = [];
            foreach($info->getArtists() as $artist) {
                $args = [];

                if($lastFMEnabled) {
                    $artistInfo = $lastFM->getArtistInfo($artist);
                    if ($artistInfo !== false) {
                        $img = $this->downloadImage($artistInfo['image'], Artist::STORAGE_PATH, $artist->getId());
                        if($img) {
                            $args['image'] = $img;
                        }
                    }
                }

                $artists[] = Artist::updateOrCreate([
                    'name' => $artist->getName()
                ], $args);
            }

            $albums = [];
            foreach($info->getAlbums() as $album) {
                $args = [];

                if($lastFMEnabled) {
                    $albumInfo = $lastFM->getAlbumInfo($album);
                    if ($albumInfo !== false) {
                        $img = $this->downloadImage($albumInfo['image'], Album::STORAGE_PATH, $album->getId());
                        if($img) {
                            $args['image'] = $img;
                        }
                    }
                }

                $albums[] = Album::updateOrCreate([
                    'title' => $album->getName()
                ], $args);
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
                return new ProcessorError("AcoustID is required but the identify process failed");
            } else {
                return $next($item);
            }
        }
    }

    private function downloadImage($imageUrl, $target, $id) {
        if(empty($imageUrl) || empty($target)) {
            return false;
        }

        $pInfo = pathinfo($imageUrl);
        $filename = 'acoustId_' . $id . '.' . $pInfo['extension'];

        Storage::put(storage_path($target . $filename), file_get_contents($imageUrl));

        return $filename;
    }

}