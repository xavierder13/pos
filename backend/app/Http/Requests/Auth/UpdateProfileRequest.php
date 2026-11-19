<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

// the signed-in user's own name and password
class UpdateProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            // a new password needs the current one
            'current_password' => ['required_with:password', 'nullable', 'current_password:sanctum'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ];
    }
}
