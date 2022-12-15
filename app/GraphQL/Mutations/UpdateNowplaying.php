<?php

namespace App\GraphQL\Mutations;

use App\Helpers\Playlist;
use App\Helpers\ShoutzorSetting;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;
use Nuwave\Lighthouse\Exceptions\AuthorizationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UpdateNowplaying
{
    public function __construct() { }

    /**
     * @param ResolveInfo $resolveInfo
     * @return string[]
     */
    #[ArrayShape(['success' => "bool", 'message' => "string"])] public function __invoke($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        /**
         * easy solution for now, just check a token vs the provided token to authenticate the player
         * TODO maybe replace this by utilizing the accesstoken system?
         */
        $storedToken = ShoutzorSetting::getSetting('shoutzor_player_token');

        // If the provided authorization header token doesn't match the stored token, throw an exception
        if($storedToken !== $context->request()->header('authorization')) {
            throw new AuthorizationException("You are not authorized to perform this action");
        }

        // Default feedback
        $success = false;

        try {
            Playlist::updateNowPlaying();

            $success = true;
            $message = 'The request has been marked as being played';
        }
        catch (Exception $e) {
            $message = "Something went wrong while marking the request as playing";
            Log::error("Failed to mark the request in the database as playing, error: {$e->getMessage()}");
        }

        return [
            'success' => $success,
            'message' => $message
        ];
    }
}
