<?php

namespace App\Modules\Comments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentInteractionRequest extends FormRequest
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
            'state' => ['required', 'string', 'in:upvote,downvote,neutral'],
        ];
    }
}
