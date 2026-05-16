<?php

namespace App\Modules\Comments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexUserCommentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page'  => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}