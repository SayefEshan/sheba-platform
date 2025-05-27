<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is authenticated and is an admin
        return Auth::user() && Auth::user()->canPerformAction('create_service');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:services,name',
            'service_category_id' => 'required|exists:service_categories,id',
            'price' => 'required|numeric|min:0|max:999999.99',
            'description' => 'required|string|max:2000',
            'duration_minutes' => 'required|integer|min:15|max:1440', // 15 minutes to 24 hours
            'is_active' => 'sometimes|boolean',
            'images' => 'nullable|array|max:5',
            'images.*' => 'string|url|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Service name is required',
            'name.unique' => 'A service with this name already exists',
            'name.max' => 'Service name cannot exceed 255 characters',
            'service_category_id.required' => 'Service category is required',
            'service_category_id.exists' => 'Selected service category does not exist',
            'price.required' => 'Service price is required',
            'price.numeric' => 'Service price must be a valid number',
            'price.min' => 'Service price cannot be negative',
            'price.max' => 'Service price cannot exceed 999,999.99',
            'description.required' => 'Service description is required',
            'description.max' => 'Service description cannot exceed 2000 characters',
            'duration_minutes.required' => 'Service duration is required',
            'duration_minutes.integer' => 'Service duration must be a whole number',
            'duration_minutes.min' => 'Service duration must be at least 15 minutes',
            'duration_minutes.max' => 'Service duration cannot exceed 24 hours (1440 minutes)',
            'images.array' => 'Images must be provided as an array',
            'images.max' => 'Cannot upload more than 5 images',
            'images.*.url' => 'Each image must be a valid URL',
            'images.*.max' => 'Image URL cannot exceed 500 characters',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'service_category_id' => 'category',
            'duration_minutes' => 'duration',
            'is_active' => 'status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        // Clean up price formatting
        if ($this->has('price')) {
            $price = $this->input('price');
            if (is_string($price)) {
                // Remove any currency symbols or spaces
                $price = preg_replace('/[^\d.]/', '', $price);
                $this->merge(['price' => $price]);
            }
        }

        // Ensure duration is an integer
        if ($this->has('duration_minutes')) {
            $this->merge(['duration_minutes' => (int) $this->input('duration_minutes')]);
        }
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to create services'
            ], 403)
        );
    }
}
