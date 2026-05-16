<?php

namespace App\Modules\Ideas\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveIdeaRequest extends FormRequest
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
        return [];
    }
}
