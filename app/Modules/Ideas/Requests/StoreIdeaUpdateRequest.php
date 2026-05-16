<?php

namespace App\Modules\Ideas\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIdeaUpdateRequest extends FormRequest
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
            'text' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'text' => trim((string) $this->input('text')),
        ]);
    }
}
