<?php

namespace App\GraphQL\Mutations;

use \Exception;
use App\Helpers\ShoutzorSetting;
use DanielDeWit\LighthouseSanctum\GraphQL\Mutations\ForgotPassword as LighthouseSanctumForgotPassword;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Log;

class ForgotPassword extends LighthouseSanctumForgotPassword {

    /**
     * @param mixed $_
     * @param array<string, mixed> $args
     * @return array<string, string>
     * @throws Exception
     */
    public function __invoke($_, array $args): array
    {
        $message = "If the username exists you will receive an email containing instructions shortly";

        $this->resetPasswordService->setResetPasswordUrl(ShoutzorSetting::resetPasswordUrl());

        try {
            $result = $this->passwordBroker->sendResetLink([
                'username' => $args['username'],
            ]);
        }
        catch(Exception $e) {
            Log::critical('Failed to send a reset password email to the user', [
                'username' => $args['username'],
                'error' => $e->getMessage()
            ]);

            throw $e;
        }

        if($result !== PasswordBroker::RESET_LINK_SENT) {
            return [
                'status'  => 'EMAIL_SENT',
                'message' => $message,
            ];
        }

        return [
            'status'  => 'EMAIL_SENT',
            'message' => $message,
        ];
    }

}