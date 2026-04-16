<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product'); // For update unique rule

        return [
            'product_name' => ['required', 'string', 'max:255'],
            'product_id' => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('products', 'product_id')->ignore($productId)
            ],
            'model_no' => ['nullable', 'string', 'max:100'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0', 'gte:cost_price'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_name.required' => 'Product name is required.',
            'product_id.required' => 'Product ID is required.',
            'product_id.unique' => 'This Product ID already exists.',
            'cost_price.min' => 'Cost price cannot be negative.',
            'selling_price.gte' => 'Selling price must be greater than or equal to cost price.',
            'stock_quantity.min' => 'Stock quantity cannot be negative.',
        ];
    }
}
