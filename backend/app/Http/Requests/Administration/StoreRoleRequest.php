<?php

namespace App\Http\Requests\Administration;

use App\Support\Rbac;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100',
                Rule::unique('roles', 'name')->where('guard_name', Rbac::GUARD)->ignore($this->route('role'))],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['integer', 'distinct', Rule::exists('permissions', 'id')->where('guard_name', Rbac::GUARD)],
        ];
    }
}
