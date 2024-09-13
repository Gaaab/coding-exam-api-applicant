<?php

namespace App\Http\Requests\Post;

use App\Services\PostService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
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
            'title' => [
                'required',
                'min:6',
                'max:255',
                Rule::unique('posts', 'title'),
            ],
            'body' => [
                'required',
            ],
            'status' => [
                'required',
                Rule::in(PostService::$statuses)
            ],
            'banner_image_url' => [
                'string',
                'url',
            ],
        ];
    }
}
