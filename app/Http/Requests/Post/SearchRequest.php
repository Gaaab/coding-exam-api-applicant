<?php

namespace App\Http\Requests\Post;

use App\Services\PostService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'query' => [
                'required',
                'string',
            ],
            'rowsPerPage' => [
                'numeric',
            ],
            'sortBy' => [
                'string',
                Rule::in('id', 'created_at', 'published_at'),
            ],
            'sortDirection' => [
                'string',
                Rule::in('asc', 'desc'),
            ],
            'paginate' => [
                'boolean',
            ],
            'status' => [
                'string',
                Rule::in(PostService::$statuses),
            ],
            'published_at' => [
                'array',
            ],
            'published_at.start_date' => [
                'date',
            ],
            'published_at.end_date' => [
                'date',
            ],
        ];
    }
}
