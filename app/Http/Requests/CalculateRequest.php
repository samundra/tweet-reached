<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'query' => 'required|string|max:255|url',
        ];
    }

    /**
     * @{inheritdoc}
     */
    public function messages()
    {
        return [
            'query.required' => 'The query field is required.',
            'query.max' => 'The query cannot exceed 255 character',
            'query.string' => 'The query field must be tring',
            'query.url' => 'The query must be valid url.'
        ];
    }
}
