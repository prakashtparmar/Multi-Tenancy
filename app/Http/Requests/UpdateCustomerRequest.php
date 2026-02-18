<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_code' => ['nullable', 'string', Rule::unique('customers')->ignore($this->customer)],
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'mobile' => ['required', 'digits:10', Rule::unique('customers')->ignore($this->customer)],
            'phone_number_2' => 'nullable|string|max:20',
            'relative_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string|max:50',
            'type' => 'required|in:farmer,buyer,vendor,dealer',
            'category' => 'required|in:individual,business',
            'company_name' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|size:15',
            'pan_number' => 'nullable|string|size:10',

            // Address
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'village' => 'nullable|string|max:255',
            'taluka' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'pincode' => 'nullable|digits:6',
            'post_office' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',

            // Agriculture
            'land_area' => 'nullable|numeric|min:0',
            'land_unit' => 'nullable|string|max:50',
            'primary_crops' => 'nullable|string', // Comma separated string
            'secondary_crops' => 'nullable|string', // Comma separated string
            'irrigation_type' => 'nullable|string|max:100',

            // Financial / Compliance
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_valid_till' => 'nullable|date',
            'aadhaar_last4' => 'nullable|digits:4',
            'kyc_completed' => 'nullable|boolean',

            // Status & Notes
            'is_active' => 'nullable|boolean',
            'is_blacklisted' => 'nullable|boolean',
            'internal_notes' => 'nullable|string|max:1000',
            'tags' => 'nullable|string',
        ];
    }
}
