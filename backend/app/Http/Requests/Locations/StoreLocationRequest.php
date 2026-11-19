<?php

namespace App\Http\Requests\Locations;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// create (POST) and update (PUT) share the rules
class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // the route's `permission:` middleware decides
    }

    /**
     * @return array<string, mixed>
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->code)) {
            $this->merge(['code' => strtoupper(trim($this->code))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('locations', 'code')->ignore($this->route('location'))],
            'name' => ['required', 'string', 'max:255', Rule::unique('locations', 'name')->ignore($this->route('location'))],
            'type' => ['required', Rule::in(Location::TYPES)],
            'address' => ['nullable', 'string', 'max:500'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
