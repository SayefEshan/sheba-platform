<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => ['required', 'string', 'regex:/^(\+8801|01)[3-9]\d{8}$/', 'max:20'],
            'customer_email' => 'nullable|email|max:100',
            'customer_address' => 'nullable|string|max:500',
            'scheduled_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'service_id.required' => 'Service is required',
            'service_id.exists' => 'Selected service does not exist',
            'customer_name.required' => 'Customer name is required',
            'customer_name.max' => 'Customer name cannot exceed 100 characters',
            'customer_phone.required' => 'Phone number is required',
            'customer_phone.regex' => 'Please enter a valid Bangladeshi phone number',
            'customer_email.email' => 'Please enter a valid email address',
            'scheduled_at.after' => 'Scheduled time must be in the future',
            'notes.max' => 'Notes cannot exceed 1000 characters',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'service_id' => 'service',
            'customer_name' => 'name',
            'customer_phone' => 'phone number',
            'customer_email' => 'email',
            'customer_address' => 'address',
            'scheduled_at' => 'scheduled time',
        ];
    }
}
