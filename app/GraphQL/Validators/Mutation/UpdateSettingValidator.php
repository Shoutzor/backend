<?php

namespace App\GraphQL\Validators\Mutation;

use App\Models\Setting;
use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator as LighthouseValidator;
use Illuminate\Support\Facades\Validator;

class UpdateSettingValidator extends LighthouseValidator
{
    public function rules(): array
    {
        $setting = Setting::findOrFail($this->arg('key'));

        return [
            'value' => [
                'required',
                Rule::prohibitedIf(fn() => $setting->readonly),
                function ($attribute, $json, $fail) use ($setting) {
                    // First check if a valid object is provided
                    if(!is_object($json)) {
                        $fail('The '.$attribute.' is not a valid object');
                    }
                    // Next check if the data property is set
                    elseif(!property_exists($json, 'data')) {
                        $fail('The '.$attribute.' is missing the required data field');
                    }
                    // Check if the data property is the only property
                    elseif(count(array_keys(get_object_vars($json))) > 1) {
                        $fail('The '.$attribute.' may not contain other fields then the data field');
                    }
                    // Check if the data propery value is of the correct type
                    elseif(gettype($json->data) !== $setting->type) {
                        $fail('The data field should be of type '.$setting->type.' got '.gettype($json->data).' instead');
                    }
                    // If array, perform additional validation checks on every item
                    elseif(
                        $setting->type === 'array' &&
                        !empty($setting->validation) &&
                        Validator::make($json->data, [ '*' => $setting->validation ])->fails()
                    ) {
                        $fail('The data field contains invalid values');
                    }
                },
            ]
        ];
    }
}