<?php

namespace App\Http\Requests\Products;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// create (POST) and update (PUT) share the rules
class StoreProductRequest extends FormRequest
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
        $this->merge([
            'sku' => is_string($this->sku) ? strtoupper(trim($this->sku)) : $this->sku,
            'barcode' => is_string($this->barcode) && trim($this->barcode) !== '' ? trim($this->barcode) : null,
            'reorder_level' => $this->reorder_level ?? 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'barcode' => ['nullable', 'string', 'max:64', Rule::unique('products', 'barcode')->ignore($this->route('product'))],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'unit' => ['required', Rule::in(Product::UNITS)],
            // money: integer centavos
            'price' => ['required', 'integer', 'min:0', 'max:99999999999'],
            'cost' => ['nullable', 'integer', 'min:0', 'max:99999999999'],
            'tax_class' => ['required', Rule::in(Product::TAX_CLASSES)],
            'sc_pwd_eligible' => ['sometimes', 'boolean'],
            'track_stock' => ['sometimes', 'boolean'],
            'reorder_level' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
