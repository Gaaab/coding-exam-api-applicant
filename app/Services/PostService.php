<?php

namespace App\Services;

use App\Dto\PostDto;
use App\Models\Post;
use App\Models\User;
use Illuminate\Validation\UnauthorizedException;

class PostService
{
    public static $statuses = ['DRAFT', 'PUBLISHED'];

    public function searchPosts(User $user, array $payload)
    {
        $posts = Post::searchPosts($payload['query'], $user);

        if (isset($payload['published_at']) && isset($payload['published_at']['start_date']) && isset($payload['published_at']['end_date'])) {
            $posts->whereBetween('published_at', [$payload['published_at']['start_date'], $payload['published_at']['end_date']]);
        }

        if (isset($payload['paginate']) && $payload['paginate'] === false) {
            return $posts->limit($payload['rowsPerPage'] ?? 10)->get();
        }

        return $posts
            ->orderBy($payload['sortBy'] ?? 'id', $payload['sortDirection'] ?? 'desc')
            ->paginate($payload['rowsPerPage'] ?? 10);
    }

    public function allPosts(User $user, $payload = [])
    {
        // Only admin can get all posts
        if ($user->role->name !== 'admin') {
            throw new UnauthorizedException("Unauthorized action", 401);
        }

        $posts = Post::query();

        if ($payload['paginate'] ?? false) {
            $posts
                ->orderBy($payload['sortBy'] ?? 'id', $payload['sortDirection'] ?? 'desc')
                ->paginate($payload['rowsPerPage'] ?? 10);
        }

        return $posts->get();
    }

    public function selfPosts(User $user, $payload = [])
    {
        $posts = $user->posts();

        if (isset($payload['paginate']) && (bool) $payload['paginate'] === false) {
            return $posts->get();
        }

        return $posts
            ->orderBy($payload['sortBy'] ?? 'id', $payload['sortDirection'] ?? 'desc')
            ->paginate($payload['rowsPerPage'] ?? 10);
    }

    public function findPost(Post $post)
    {
        return $post;
    }

    public function createPost(User $user, PostDto $payload): Post
    {
        return $user->posts()->create($payload->toArray());
    }

    public function updatePost(Post $post, array $payload)
    {
        $post->fill($payload);

        if ($payload['status'] === 'PUBLISHED') {
            $post->forceFill(['published_at' => now()]);
        } else {
            $post->forceFill(['published_at' => null]);
        }

        $post->update();

        return $post->fresh('user');


        return $post->fresh(['user']);
    }

    public function publishPost(Post $post) {}

    public function draftPost(Post $post) {}

    public function archivePost(Post $post) {}

    public function restorePost(Post $post) {}
}
