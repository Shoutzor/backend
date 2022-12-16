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
use Illuminate\Support\Str;

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

        try {
            $info = $acoustID->getMediaInfo($upload->getFilePath());

            // Audio Fingerprint lookup returned no results
            if($info === null) {
                throw new Exception("AcoustID returned no results");
            }

            $artists = [];
            foreach($info->getArtists() as $artist) {
                $artists[] = Artist::firstOrCreate([
                    'name' => $artist->getName()
                ]);
            }

            $albums = [];
            foreach($info->getAlbums() as $album) {
                $a = Album::firstOrCreate([
                    'title' => $album->getName()
                ]);

                if(!empty($a->image) && $album->getImage()) {
                    $img = $this->downloadImage($album->getImage(), Album::STORAGE_PATH, Str::uuid());
                    if ($img) {
                        $a->update([
                            'image' => $img
                        ]);
                    }
                }

                $albums[] = $a;
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

    /**
     * @TODO fix this horrible mess
     *
     * @param $imageUrl
     * @param $target
     * @param $id
     * @return string|null
     */
    private function downloadImage($imageUrl, $target, $id) {
        if(empty($imageUrl) || empty($target)) {
            return null;
        }

        try {
            $pInfo = pathinfo($imageUrl);
            $filename = 'acoustId_' . $id . '.' . $pInfo['extension'];

            $targetPath = storage_path('app/' . $target . $filename);

            $download = file_get_contents($imageUrl);
            if(!$download) {
                throw new Exception("Failed downloading the image");
            }

            file_put_contents($targetPath, $download);

            return $filename;
        }
        catch (Exception $e) {
            Log::error("An error occured while downloading the album image", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

}