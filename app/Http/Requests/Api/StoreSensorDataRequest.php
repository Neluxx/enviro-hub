<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreSensorDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by the middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'node_uuid' => ['required', 'string', 'uuid'],
            'temperature' => ['nullable', 'numeric', 'between:-100,100'],
            'humidity' => ['nullable', 'numeric', 'between:0,100'],
            'pressure' => ['nullable', 'integer', 'min:0'],
            'carbon_dioxide' => ['nullable', 'integer', 'min:0'],
            'measured_at' => ['required', 'date'],
        ];
    }

    /**
     * Ensure failed validation returns a JSON response.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
