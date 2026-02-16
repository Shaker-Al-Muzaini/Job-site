<?php

namespace App\Presentation\Http\Requests\Page;

use Illuminate\Foundation\Http\FormRequest;

class PageIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|string|in:id,name,created_at',
            'sort_dir' => 'nullable|string|in:asc,desc',
            'limit' => 'nullable|integer|min:1|max:100',
        ];
    }
}
