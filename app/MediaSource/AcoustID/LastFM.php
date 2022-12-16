<?php

namespace App\MediaSource\AcoustID;

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

    public function getArtistInfo(AcoustIDArtist $artist) {
        //Make sure the LastFM API is enabled
        if($this->isEnabled() === false) {
            return false;
        }

        //Make the API call and fetch the data
        $result = $this->apiCall('artist.getinfo', ['mbid' => $artist->getId()]);

        if($result === false) {
            return false;
        }

        //If an error occured, return false
        if(isset($result['error']) || !isset($result['artist'])) {
            return false;
        }

        $result = $result['artist'];

        //Remove unneeded values
        if(isset($result['stats'])) unset($result['stats']);
        if(isset($result['similar'])) unset($result['similar']);
        if(isset($result['tags'])) unset($result['tags']);

        //Make sure required values are always set though
        if(!isset($result['bio'])) $result['bio'] = array();
        if(!isset($result['bio']['summary'])) $result['bio']['summary'] = '';
        if(!isset($result['image'])) $result['image'] = array();

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
            $result['image'] = '';
        }

        return $result;
    }

    public function getAlbumInfo(AcoustIDAlbum $album) {
        //Make sure the LastFM API is enabled
        if($this->isEnabled() === false) {
            return false;
        }

        //Make the API call and fetch the data
        $result = $this->apiCall('album.getinfo', ['mbid' => $album->getId()]);

        if($result === false) {
            return false;
        }

        //If an error occured, return false
        if(isset($result['error']) || !isset($result['album'])) {
            return false;
        }

        $result = $result['album'];

        //Remove unneeded values
        if(isset($result['listeners'])) unset($result['listeners']);
        if(isset($result['playcount'])) unset($result['playcount']);
        if(isset($result['tracks'])) unset($result['tracks']);
        if(isset($result['tags'])) unset($result['tags']);

        //Make sure required values are always set though
        if(!isset($result['wiki'])) $result['wiki'] = array('summary' => '');
        if(!isset($result['wiki']['summary'])) $result['wiki']['summary'] = '';
        if(!isset($result['image'])) $result['image'] = array();

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
            $result['image'] = '';
        }

        return $result;
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