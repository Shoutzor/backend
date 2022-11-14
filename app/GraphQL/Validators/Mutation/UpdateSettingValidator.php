<?php

namespace App\GraphQL\Validators\Mutation;

use App\Models\Setting;
use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class UpdateSettingValidator extends Validator
{
    public function rules(): array
    {
        $setting = Setting::findOrFail($this->arg('key'));

        return [
            'value' => [
                'required',
                Rule::prohibitedIf(fn() => $setting->readonly),
            ]
        ];
    }
}