<?php

namespace App\Helpers;

use App\Models\Media;
use App\Models\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Nuwave\Lighthouse\Execution\Utils\Subscription;

class Playlist
{
    /**
     * Will fetch the amount of requested items from the queue
     * if there are less than the amount of requested items in the queue, the AutoDJ
     * will fill up the queue until the minimum is reached
     * @param int $items
     * @return Collection
     */
    public static function getQueue(int $items = 1): Collection
    {
        $queue = Request::query()
            ->whereNull('played_at')
            ->orderBy('requested_at', 'DESC')
            ->limit($items)
            ->get();

        $queueCount = $queue->count();

        if($queueCount < $items) {
            $queue = $queue->merge(static::autoDjQueueTrack($items - $queueCount));
        }

        return $queue;
    }

    /**
     * Marks the top item in the queue as "now playing"
     * @return void
     */
    public static function updateNowPlaying(): void
    {
        $topRequest = Request::query()
            ->whereNull('played_at')
            ->orderBy('requested_at', 'DESC')
            ->limit(1)
            ->firstOrFail();

        $topRequest->update([
            'played_at' => now()
        ]);

        Subscription::broadcast('requestPlayed', $topRequest);
    }

    /**
     * Will queue items as the AutoDJ
     *
     * @param int $amount the amount of items to queue
     * @return Collection
     */
    private static function autoDjQueueTrack(int $amount = 1): Collection
    {
        $queue = collect([]);

        for($i = 0; $i < $amount; $i++) {
            $m = static::getRandomTrack(true);

            if(!is_null($m)) {
                $request = Request::create([
                    'media_id' => $m->id,
                    'played_at' => null
                ]);

                Subscription::broadcast('requestAdded', $request);
                $queue->add($request);
            }
        }

        return $queue;
    }

    /**
     * Will select a random track based on certain conditions to prevent repetition
     * @param $forced boolean if set to true the function will grab a random track if there are no tracks
     * available that match the conditions.
     * @return Builder|Model|null
     */
    private static function getRandomTrack(bool $forced = false): Model|Builder|null
    {
        $requestHistoryTime = now()->subMinutes(ShoutzorSetting::getSetting('shoutzor_request_media_delay'));
        $artistHistoryTime = now()->subMinutes(ShoutzorSetting::getSetting('shoutzor_request_artist_delay'));

        try {
            //Build a list of media id's that are available to play, next, randomly pick one
            $m = Media::query()
                ->select('media.*')
                ->leftJoin('artist_media', 'artist_media.media_id', '=', 'media.id')
                ->leftJoin('artists', 'artist_media.artist_id', '=', 'artists.id')
                ->leftJoin('requests', 'requests.media_id', '=',  'media.id')
                //Exclude all media_id's that are in already in queue or have recently been played
                ->whereNotIn('media.id', function ($query) use ($requestHistoryTime) {
                    $query
                        ->select('requests.media_id')
                        ->from('requests')
                        ->whereNull('requests.played_at')
                        ->orWhere('requests.played_at', '>', $requestHistoryTime);
                })
                //Next, exclude all artists that have recently been played (or are queued right now) and exclude those too
                ->whereNotIn('artist_media.artist_id', function ($query) use ($artistHistoryTime) {
                    $query
                        ->select('artist_media.artist_id')
                        ->from('artist_media')
                        ->leftJoin('requests', 'requests.media_id', '=', 'artist_media.media_id')
                        ->whereNull('requests.played_at')
                        ->orWhere('requests.played_at', '>', $artistHistoryTime);
                })
                ->inRandomOrder(microtime(true))
                ->firstOrFail();

            return $m;
        }
        // When no results were found, this can happen if there are too few songs
        // if they have all been played too recently, none of them will match the set conditions
        catch(ModelNotFoundException $e) {
            Log::error("No tracks available that havent been recently played!");

            // If forced is true, just grab a random file without any restrictions (ie: panic-mode)
            if($forced) {
                Log::info("Forced is set to true, playing random track");

                $m = Media::query()
                    ->inRandomOrder()
                    ->first();

                return $m;
            }
        }
        catch (\Exception $e) {
            return null;
        }

        return null;
    }
}