<?php

namespace App\GraphQL\Validators\Mutation;

use App\Models\Role;
use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class UpdateRoleValidator extends Validator
{
    public function rules(): array
    {
        $role = Role::findOrFail($this->arg('id'));

        return [
            'name' => [
                'sometimes',
                'required',
                'alpha_dash',
                Rule::prohibitedIf(fn() => $role->protected === true),
                Rule::unique('roles', 'name')->ignore($this->arg('id'), 'id')
            ],
            'description' => [
                'sometimes',
                'required',
                Rule::prohibitedIf(fn() => $role->protected === true)
            ]
        ];
    }
}