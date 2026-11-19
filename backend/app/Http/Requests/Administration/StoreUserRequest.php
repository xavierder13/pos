<?php

namespace App\Http\Requests\Administration;

use App\Support\Rbac;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // the route's `permission:` middleware decides
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'confirmed', Password::min(8)],
            'active' => [$this->isMethod('post') ? 'sometimes' : 'required', 'boolean'],
            // "Require password change at next sign-in"
            'must_change_password' => ['sometimes', 'boolean'],
            'roles' => ['present', 'array'],
            'roles.*' => ['integer', 'distinct', Rule::exists('roles', 'id')->where('guard_name', Rbac::GUARD)],
        ];
    }
}
