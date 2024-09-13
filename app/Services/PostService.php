<?php

namespace App\Services;

use App\Dto\PostDto;
use App\Models\Post;
use App\Models\User;
use Illuminate\Validation\UnauthorizedException;

class PostService
{
    public static $statuses = ['DRAFT', 'PUBLISHED'];

    public function allPosts(User $user, $options = [])
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

    public function selfPosts(User $user, $options = [])
    {
        $posts = $user->posts();

        if ($payload['paginate'] ?? false) {
            $posts
                ->orderBy($payload['sortBy'] ?? 'id', $payload['sortDirection'] ?? 'desc')
                ->paginate($payload['rowsPerPage'] ?? 10);
        }

        return $posts->get();
    }

    public function findPost(Post $post)
    {

    }

    public function createPost(User $user, PostDto $payload): Post
    {
        return $user->posts()->create($payload->toArray());
    }

    public function updatePost(Post $post, PostDto $payload)
    {
        $post->fill($payload->toArray())->update();

        return $post->fresh(['user']);
    }

    public function publishPost(Post $post)
    {

    }

    public function draftPost(Post $post)
    {

    }

    public function archivePost(Post $post)
    {

    }

    public function restorePost(Post $post)
    {

    }
}
