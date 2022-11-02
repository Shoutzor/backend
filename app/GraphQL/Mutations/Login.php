<?php

namespace App\GraphQL\Mutations;

use Exception;
use App\Helpers\ShoutzorSetting;
use DanielDeWit\LighthouseSanctum\GraphQL\Mutations\Login as LighthouseSanctumLogin;
use DanielDeWit\LighthouseSanctum\Exceptions\HasApiTokensException;
use DanielDeWit\LighthouseSanctum\Traits\CreatesUserProvider;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Config\Repository as Config;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

class Login extends LighthouseSanctumLogin
{

    /**
     * @param mixed $_
     * @param array<string, string> $args
     * @return string[]
     * @throws Exception
     */
    public function __invoke($_, array $args): array
    {
        $userProvider = $this->createUserProvider();

        $identificationKey = $this->getConfig()
            ->get('lighthouse-sanctum.user_identifier_field_name', 'email');

        $user = $userProvider->retrieveByCredentials([
            $identificationKey => $args[$identificationKey],
            'password' => $args['password'],
        ]);

        if (! $user || ! $userProvider->validateCredentials($user, $args)) {
            throw new AuthenticationException('The provided credentials are incorrect.');
        }

        if (! $user instanceof HasApiTokens) {
            throw new HasApiTokensException($user);
        }

        // Check if email validation is required
        if (
            ShoutzorSetting::isEmailVerificationRequired() && 
            ! $user->hasVerifiedEmail()
            ) {
            throw new AuthenticationException('Your email address is not verified.');
        }

        // Check if the user approval is required
        if(
            ShoutzorSetting::isManualApproveRequired() && 
            !$user->approved
            ) {
            throw new AuthenticationException("Your account has not been approved yet");
        }

        // Check if the user has been blocked
        if($user->blocked) {
            throw new AuthenticationException("Your account is blocked");
        }

        return [
            'token' => $user->createToken('default')->plainTextToken,
        ];
    }
}