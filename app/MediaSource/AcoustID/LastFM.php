<?php

namespace App\MediaSource\AcoustID;

use App\Exceptions\AcoustIDException;
use App\Helpers\ShoutzorSetting;
use App\MediaSource\AcoustID\Result\AcoustIDAlbum;
use App\MediaSource\AcoustID\Result\AcoustIDArtist;
use Illuminate\Support\Facades\Http;

/**
 * @TODO refactor this class
 */
class LastFM {
    public function isEnabled() {
        return ShoutzorSetting::getSetting('acoustid_use_lastfm');
    }

    public function getTrackInfo($title, $artist) {
        if($this->isEnabled() === false) {
            return null;
        }

        $data = $this->apiCall('track.getInfo', ['track' => $title, 'artist' => $artist]);

        if(isset($data['error'])) {
            return null;
        }

        $data = $data['track'];

        $result = new AcoustIDResult($data['name']);

        $artist = $this->getArtistInfo($data);
        if($artist) {
            $result->addArtist($artist);
        }

        $album = $this->getAlbumInfo($data);
        if($album) {
            $result->addAlbum($album);
        }

        return $result;
    }

    private function getArtistInfo($result): ?AcoustIDArtist
    {
        if(!array_key_exists('artist', $result)) {
            return null;
        }

        $result = $result['artist'];

        return new AcoustIDArtist(
            $result['name']
        );
    }

    public function getAlbumInfo($result) {
        if(!array_key_exists('album', $result)) {
            return null;
        }

        $result = $result['album'];

        // Prevent breakage
        if(!array_key_exists('image', $result)) $result['image'] = array();
        $images = array();

        //Build our own associative array of images and their sizes
        foreach($result['image'] as $image) {
            if(!isset($image['size'])) continue;
            $images[$image['size']] = $image['#text'];
        }

        //Try to get the most suitable size picture
        if(isset($images['large'])) {
            $result['image'] = $images['large'];
        }
        elseif(isset($images['medium'])) {
            $result['image'] = $images['medium'];
        }
        elseif(isset($images['extralarge'])) {
            $result['image'] = $images['extralarge'];
        }
        elseif(isset($images['mega'])) {
            $result['image'] = $images['mega'];
        }
        else {
            //Size small is too small to be usable
            $result['image'] = null;
        }

        $album = new AcoustIDAlbum(
            $result['title']
        );

        if($result['image']) {
            $album->setImage($result['image']);
        }

        return $album;
    }

    private function apiCall($method, $params) {
        $response = Http::get('http://ws.audioscrobbler.com/2.0/', [
            ...$params,
            'method' => $method,
            'format' => 'json',
            'api_key' => ShoutzorSetting::getSetting('acoustid_lastfm_apikey')
        ]);

        if($response->ok() === false) {
            return false;
        }

        return $response->json();
    }
}