<?php

namespace App\GraphQL\Queries;

use App\Helpers\Playlist;
use App\Helpers\ShoutzorSetting;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Exceptions\AuthorizationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

final class GetQueue
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
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

        return Playlist::getQueue($args['items']);
    }
}
