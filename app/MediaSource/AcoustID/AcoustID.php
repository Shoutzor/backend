<?php
namespace App\MediaSource\AcoustID;

use App\Exceptions\AcoustIDException;
use App\Exceptions\AcoustIDNoResultsException;
use App\Helpers\ShoutzorSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class AcoustID {

    private readonly string $apiKey;

    public function __construct() {
        $this->apiKey = ShoutzorSetting::getSetting('acoustid_apikey');
    }

    public function getMediaInfo($filename) : AcoustIDResult | null {
        // Get the fingerprint from the media file
        $data = $this->getFileFingerprint($filename);

        // Errorchecking
        if(!$data) {
            return null;
        }

        // Get matching information for the provided fingerprint
        $data = $this->lookup($data->duration, $data->fingerprint);

        // Check if the result was null
        if(!$data) {
            return null;
        }

        //Return the results
        return $this->buildResult($data);
    }

    public function getFileFingerprint($file) {
        $process = new Process(['fpcalc', '-json', escapeshellarg($file)]);

        // Execute the process, then check if the result was successful
        if($process->run() !== 0) {
            Log::error("An error occured while calculating the audio fingerprint");
            throw new AcoustIDException("An error occured while calculating the audio fingerprint");
        }

        return json_decode($process->getOutput());
    }

    public function lookup($duration, $fingerprint) : AcoustIDResult | null {
        $response = Http::get('http://api.acoustid.org/v2/lookup', [
            'client' => $this->apiKey,
            'meta' => 'compress+recordings+sources+releasegroups',
            'duration' => $duration,
            'fingerprint' => $fingerprint
        ]);

        if($response->ok() === false) {
            Log::error("An error occurred while contacting the AcoustID API");
            throw new AcoustIDException("An error occurred while contacting the AcoustID API");
        }

        // Get the JSON response
        $data = $response->json();

        // Check if the API returned an error
        if($data->status === "error") {
            Log::error("AcoustID API returned an error: " . $data?->error?->message);
            throw new AcoustIDException("AcoustID API returned an error: " . $data?->error?->message);
        }

        $highestScore = 0;
        $result = null;

        // Check every result and determine the highest scoring result
        foreach($data as $key=>$value) {
            // Ensure the result contains a recording
            if(!isset($value->recordings)) {
                continue;
            }

            // If the item has a higher score, set it as the new result
            if($value->score > $highestScore) {
                $result = $value;
                $highestScore = $value->score;
            }
        }

        //Make sure the results list is not empty (this happens when it cant identify the music)
        if($result === null) {
            Log::info("AcoustID API returned no (valid) results");
            return null;
        }

        // ensure the result score isn't too low
        if($highestScore < 0.5) {
            Log::info("AcoustID API result score is too low");
            throw new AcoustIDNoResultsException("AcoustID API result score is too low");
        }

        // Return the best result
        return $result;
    }

    private function buildResult($data) : AcoustIDResult {
        $result = new AcoustIDResult($data->title);

        //Get the media file artists
        if(isset($data->artists)) {
            foreach($data->artists as $artist) {
                $result->addArtist($artist->name);
            }
        }

        //Get the media file albums
        if(isset($data->releasegroups)) {
            foreach($data->releasegroups as $release) {
                // Validate the releasegroup is an album type
                if(!isset($release->type)) continue;
                if(strtolower($release->type) !== "album") continue;

                $result->addAlbum($release->title);
            }
        }

        return $result;
    }
}