<?php

namespace App\Modules\Comments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIdeaCommentRequest extends FormRequest
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
            'text' => ['required', 'string', 'min:1', 'max:1000'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'root_comment_id' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'root_comment_id.prohibited' => 'The root_comment_id field is managed by the server.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'text' => trim((string) $this->input('text')),
        ]);
    }
}
