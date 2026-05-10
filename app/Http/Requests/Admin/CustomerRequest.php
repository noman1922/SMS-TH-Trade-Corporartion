<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check(); // Admin and Staff can both create/edit
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            // CUSTOMER MODULE IMPROVEMENT
            'customer_id' => [
                'nullable',
                'string',
                'max:20',
            ],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'hospital_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'mobile' => [
                'required', 
                'string', 
                'max:20', 
                Rule::unique('customers', 'mobile')->ignore($customer)
            ],
            'previous_due' => ['required', 'numeric', 'min:0'],
        ];
    }
}
