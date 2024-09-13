<?php

namespace Database\Factories;

use App\Models\Post;
use Closure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status  = collect(['DRAFT', 'PUBLISHED'])->random();

        return [
            'banner_image_url' => $this->faker->imageUrl(800, 400, 'business', true),
            'user_id' => collect([1, 2])->random(),
            'title' => $this->faker->unique()->sentence(),
            'body' => $this->faker->paragraph,
            'status' => $status,
            'published_at' => $status === 'PUBLISHED' ? now() : null,
        ];
    }
}
