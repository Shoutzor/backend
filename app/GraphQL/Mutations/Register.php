<?php

namespace App\GraphQL\Mutations;

use Exception;
use App\Helpers\ShoutzorSetting;
use DanielDeWit\LighthouseSanctum\GraphQL\Mutations\Register as LighthouseSanctumRegister;
use DanielDeWit\LighthouseSanctum\Exceptions\HasApiTokensException;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Facades\Log;
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
            [
                ...$this->getPropertiesFromArgs($args),
                // Invert the value of whether manual approvement is required
                // Manual approvement required -> results in "false", ie: "not approved"; and vice-versa.
                'approved' => !ShoutzorSetting::isManualApproveRequired()
            ]
        );

        // Set the user's default role
        $user->assignRole('user');
        $user->save();

        // Check if users are required to verify their email
        if (ShoutzorSetting::isEmailVerificationRequired()) {
            $this->emailVerificationService->setVerificationUrl(ShoutzorSetting::emailVerificationUrl());

            try {
                $user->sendEmailVerificationNotification();
            }
            catch(Exception $e) {
                Log::critical('Failed to send verification email to the user', [
                    'user' => $user,
                    'error' => $e->getMessage()
                ]);

                throw $e;
            }

            return [
                'token'  => null,
                'status' => 'MUST_VERIFY_EMAIL',
            ];
        }
        else {
            // No verification required
            $user->email_verified_at = now();
        }

        // Save the user's "email_verified_at" and "approved" values
        $user->save();

        if (! $user instanceof HasApiTokens) {
            throw new HasApiTokensException($user);
        }

        if(ShoutzorSetting::isManualApproveRequired()) {
            return [
                'token' => null,
                'status' => 'MANUAL_APPROVE_REQUIRED'
            ];
        }
        else {
            return [
                'token' => $user->createToken('default')->plainTextToken,
                'status' => 'SUCCESS',
            ];
        }
    }
}