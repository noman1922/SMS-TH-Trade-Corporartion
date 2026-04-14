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
        $customerId = $this->route('customer');

        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'hospital_name' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'mobile' => [
                'required', 
                'string', 
                'max:20', 
                Rule::unique('customers', 'mobile')->ignore($customerId)
            ],
            'previous_due' => ['required', 'numeric', 'min:0'],
        ];
    }
}
