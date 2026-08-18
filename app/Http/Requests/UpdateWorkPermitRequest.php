<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkPermitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller via Policy
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'permit_type_id' => ['nullable', 'integer', 'exists:permit_types,id'],
            'plant_id'       => ['nullable', 'integer', 'exists:plants,id'],
            'building_id'    => ['nullable', 'integer', 'exists:buildings,id'],
            'valid_from'     => ['nullable', 'date'],
            'valid_until'    => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];
    }
}
