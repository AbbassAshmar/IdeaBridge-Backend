<?php

namespace App\Modules\Ideas\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexIdeasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
            'page' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'nullable', 'in:asc,desc'],
        ];
    }
}