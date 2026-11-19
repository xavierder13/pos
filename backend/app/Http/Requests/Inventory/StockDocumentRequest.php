<?php

namespace App\Http\Requests\Inventory;

use App\Models\StockDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// One request for the three postings; the route's name says which (inventory.receive / adjust / transfer).
// lines.*.quantity: decimal with up to 3 places; signed for adjustments, positive otherwise.
// lines.*.unit_cost: integer centavos (receipts).
class StockDocumentRequest extends FormRequest
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
        $kind = str($this->route()->getName())->afterLast('.')->toString();
        $qty = $kind === 'adjust'
            ? ['required', 'numeric', 'decimal:0,3', 'not_in:0', 'between:-999999,999999']
            : ['required', 'numeric', 'decimal:0,3', 'gt:0', 'max:999999'];

        return [
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'to_location_id' => [Rule::requiredIf($kind === 'transfer'), 'nullable', 'integer', 'exists:locations,id', 'different:location_id'],
            'reason' => [Rule::requiredIf($kind === 'adjust'), 'nullable', Rule::in(StockDocument::REASONS)],
            'supplier' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
            'lines' => ['required', 'array', 'min:1', 'max:200'],
            'lines.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'lines.*.quantity' => $qty,
            'lines.*.unit_cost' => ['nullable', 'integer', 'min:0', 'max:99999999999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lines.required' => 'Add at least one product.',
            'lines.*.product_id.distinct' => 'A product is listed twice.',
            'to_location_id.different' => 'Choose two different locations.',
        ];
    }
}
