<?php

namespace App\GraphQL\Mutations;

use \Exception;
use App\Helpers\ShoutzorSetting;
use DanielDeWit\LighthouseSanctum\GraphQL\Mutations\ResendEmailVerification as LighthouseSanctumResendEmailVerification;
use Illuminate\Support\Facades\Log;

class ResendEmailVerification extends LighthouseSanctumResendEmailVerification {

    /**
     * @param mixed $_
     * @param array<string, mixed> $args
     * @return array<string, string>
     */
    public function __invoke($_, array $args): array
    {
        $userProvider = $this->createUserProvider();

        $user = $userProvider->retrieveByCredentials([
            'username' => $args['username'],
        ]);

        if ($user && ShoutzorSetting::isEmailVerificationRequired() && !$user->hasVerifiedEmail()) {
            $this->emailVerificationService->setVerificationUrl(ShoutzorSetting::emailVerificationUrl());

            try {
                $user->sendEmailVerificationNotification();
            } catch(Exception $e) {
                Log::critical('Failed to re-send verification email to the user', [
                    'user' => $user,
                    'error' => $e->getMessage()
                ]);

                throw $e;
            }
        }

        return [
            'status' => 'EMAIL_SENT',
        ];
    }

}