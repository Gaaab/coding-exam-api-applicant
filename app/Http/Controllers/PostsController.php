<?php

namespace App\Http\Controllers;

use App\Dto\PostDto;
use App\Http\Requests\Post\CreateRequest as CreatePostRequest;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use OpenApi\Attributes as OA;

class PostsController extends Controller
{
    public function __construct(protected PostService $postService)
    {
    }

    public function selfPosts(Request $request)
    {
        return $this->postService->selfPosts($request->user());
    }

    public function allPosts(Request $request)
    {
        // Move this to form request
        $user = $request->user();

        if (!$user || $user->role->name !== 'admin') {
            throw new UnauthorizedException("Unauthorize access error.", 401);
        }

        return $this->postService->allPosts($request->user());
    }

    public function createPost(CreatePostRequest $request)
    {
        $payload = PostDto::fromRequest($request);

        return $this->postService->createPost($request->user(), $payload);
    }

    public function findPost(Post $post)
    {
        return $this->postService->findPost($post);
    }
}
