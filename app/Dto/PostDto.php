<?php

namespace App\Dto;

use Carbon\Carbon;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PostDto
{
    public function __construct(
        public string $title,
        public string $body,
        public string $status,
        public ?string $banner_image_url = null,
        public ?Carbon $published_at = null
    )
    {
    }

    /**
     * Create a new PostDto instance from a request.
     *
     * @param Request $request
     * @return static
     */
    public static function fromRequest(Request $request)
    {
        return new static(...$request->safe()->all());
    }

    /**
     * Convert the PostDto to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return (array) $this;
    }
}
