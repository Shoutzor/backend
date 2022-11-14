<?php

namespace App\GraphQL\Validators\Mutation;

use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class UpdateUserValidator extends Validator
{
    public function rules(): array
    {
        return [
            'username' => [
                'bail',
                'sometimes',
                'required',
                'alpha_num',
                Rule::unique('users', 'username')->ignore($this->arg('id'), 'id')
            ],
            'email' => [
                'bail',
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->arg('id'), 'id')
            ],
            /**
             * Only allow this value to be set to true.
             * Once a user is approved he should not be un-approved. Block the user instead.
             */
            'approved' => [
                'bail',
                'sometimes',
                'required',
                'boolean',
                Rule::prohibitedIf(fn() => $this->arg('approved') !== true)
            ],
            'blocked' => [
                'sometimes',
                'required',
                'boolean'
            ],
            'permissions' => [
                'sometimes',
                'array'
            ],
            'permissions.*' => [
                'sometimes',
                'exists:permissions,id'
            ],
            'roles' => [
                'sometimes',
                'array'
            ],
            'roles.*' => [
                'sometimes',
                'exists:roles,id'
            ]
        ];
    }
}