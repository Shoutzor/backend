<?php

namespace App\GraphQL\Mutations;

use Exception;
use App\Helpers\ShoutzorSetting;
use DanielDeWit\LighthouseSanctum\GraphQL\Mutations\Register as LighthouseSanctumRegister;
use DanielDeWit\LighthouseSanctum\Contracts\Services\EmailVerificationServiceInterface;
use DanielDeWit\LighthouseSanctum\Exceptions\HasApiTokensException;
use DanielDeWit\LighthouseSanctum\Traits\CreatesUserProvider;
use Illuminate\Auth\AuthManager;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Contracts\HasApiTokens;

class Register extends LighthouseSanctumRegister
{
    /**
     * @param mixed $_
     * @param array<string, mixed> $args
     * @return array<string, string|null>
     * @throws Exception
     */
    public function __invoke($_, array $args): array
    {
        /** @var EloquentUserProvider $userProvider */
        $userProvider = $this->createUserProvider();

        $user = $this->saveUser(
            $userProvider->createModel(),
            $this->getPropertiesFromArgs($args),
        );
        
        // Check if users are required to verify their email
        if (ShoutzorSetting::isEmailVerificationRequired()) {
            if (isset($args['verification_url'])) {
                /** @var array<string, string> $verificationUrl */
                $verificationUrl = $args['verification_url'];

                $this->emailVerificationService->setVerificationUrl($verificationUrl['url']);
            }

            $user->sendEmailVerificationNotification();

            return [
                'token'  => null,
                'status' => 'MUST_VERIFY_EMAIL',
            ];
        }
        else {
            // No verification required
            $user->email_verified_at = now();
        }

        // Invert the value of whether manual approvement is required
        // Manual approvement required -> results in "false", ie: "not approved"; and vice-versa.
        $user->approved = !ShoutzorSetting::isManualApproveRequired();

        // Save the user's "email_verified_at" and "approved" values
        $user->save();

        if (! $user instanceof HasApiTokens) {
            throw new HasApiTokensException($user);
        }

        return [
            'token'  => $user->createToken('default')->plainTextToken,
            'status' => 'SUCCESS',
        ];
    }
}