<?php
namespace App\MediaSource\AcoustID;

use App\Exceptions\AcoustIDException;
use App\Exceptions\AcoustIDNoResultsException;
use App\Exceptions\AcoustIDScoreTooLowException;
use App\Helpers\ShoutzorSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * @TODO Refactor this class
 */
class AcoustID {

    private readonly string $apiKey;

    public function __construct() {
        $this->apiKey = ShoutzorSetting::getSetting('acoustid_apikey');
    }

    public function getMediaInfo($filename) : AcoustIDResult | null {
        try {
            // Get the fingerprint from the media file
            $data = $this->getFileFingerprint($filename);

            // Errorchecking
            if(!$data) {
                return null;
            }

            // Get matching information for the provided fingerprint
            return $this->lookup(round($data->duration), $data->fingerprint);
        }
        /*
         * Specifically catch the no-results exceptions. Only those indicate a successful lookup
         * other exceptions indicate something went wrong.
         */
        catch(AcoustIDNoResultsException $exception) {
            return null;
        }
    }

    public function getFileFingerprint($file) {
        $process = new Process(['fpcalc', '-json', $file]);
        $process->run();
        $output = $process->getOutput();

        // Execute the process, then check if the result was successful
        if($process->getExitCode() !== 0) {
            Log::error("An error occured while calculating the audio fingerprint", [
                'exitcode' => $process->getExitCode(),
                'exitcodetext' => $process->getExitCodeText(),
                'output' => $output
            ]);
            throw new AcoustIDException("An error occured while calculating the audio fingerprint");
        }

        return json_decode($output);
    }

    /**
     * Perform a lookup against the AcoustID API using the Fingerprint and duration
     * calculated by fpcalc
     *
     * @param $duration integer the duration of the song, must be a rounded number
     * @param $fingerprint string the fingerprint of the song calculated using fpcalc
     * @return AcoustIDResult
     * @throws AcoustIDException thrown if the API call fails
     * @throws AcoustIDNoResultsException thrown if the API returned no (valid) results
     * @throws AcoustIDScoreTooLowException thrown if the score of the result returned by the API is too low
     */
    public function lookup($duration, $fingerprint) : AcoustIDResult {
        $url = 'http://api.acoustid.org/v2/lookup?client=' . $this->apiKey;
        $url .= '&meta=compress+recordings+sources+releasegroups&duration=' . $duration;
        $url .= '&fingerprint=' . $fingerprint;

        // Guzzle encodes the "+" character with no (easy) way of disabling it..
        // Will have to use curl here.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); // Assuming you're requesting JSON
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        if($response === false) {
            throw new AcoustIDException("Failed to fetch API result using Curl");
        }

        $data = json_decode($response, true);

        $highestScore = 0;
        $result = null;

        // Check every result and determine the highest scoring result
        foreach($data['results'] as $value) {
            // Ensure the result contains a recording
            if(!array_key_exists('recordings', $value)) {
                continue;
            }

            // If the item has a higher score, set it as the new result
            if($value['score'] > $highestScore) {
                $result = $value;
                $highestScore = $value['score'];
            }
        }

        //Make sure the results list is not empty (this happens when it cant identify the music)
        if($result === null) {
            Log::info("AcoustID API returned no (valid) results");
            throw new AcoustIDNoResultsException("AcoustID API returned no (valid) results");
        }

        // ensure the result score isn't too low
        if($highestScore < 0.5) {
            Log::info("AcoustID API result score is too low, got: $highestScore");
            throw new AcoustIDScoreTooLowException("AcoustID API result score is too low, got: $highestScore");
        }

        // Return the best result
        return $this->buildResult($result);
    }

    private function buildResult($data) : AcoustIDResult {
        if(!array_key_exists('id', $data)) {
            throw new AcoustIDNoResultsException("AcoustID API returned no (valid) results");
        }

        $result = null;
        foreach($data['recordings'] as $recording) {
            if(!array_key_exists('title', $recording)) continue;
            if(!array_key_exists('artists', $recording) || count($recording['artists']) == 0) continue;
            if(array_key_exists('releasegroups',$recording)) {
                $skip = false;

                foreach($recording['releasegroups'] as $releasegroup) {
                    if(array_key_exists('secondarytypes', $releasegroup)) {
                        $skip = true;
                        break;
                    }
                }

                if($skip) continue;
            }

            $result = $recording;
            break;
        }

        if(!$result) {
            throw new AcoustIDNoResultsException("AcoustID API returned no (valid) results");
        }

        $lastFm = new LastFM();
        $result = $lastFm->getTrackInfo($result['title'], $result['artists'][0]['name']);

        if(!$result) {
            throw new AcoustIDNoResultsException("LastFM API returned no (valid) results");
        }

        return $result;
    }
}