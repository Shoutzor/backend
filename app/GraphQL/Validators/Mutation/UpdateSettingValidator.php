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
                function ($attribute, $json, $fail) {
                    if(!is_object($json)) {
                        $fail('The '.$attribute.' is not a valid object');
                    }
                    elseif(!property_exists($json, 'data')) {
                        $fail('The '.$attribute.' is missing the required data field');
                    }
                    elseif(count(array_keys(get_object_vars($json))) > 1) {
                        $fail('The '.$attribute.' may not contain other fields then the data field');
                    }
                },
            ]
        ];
    }
}