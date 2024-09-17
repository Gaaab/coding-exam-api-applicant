<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\CreateRequest as CreatePostRequest;
use App\Http\Requests\Post\SearchRequest as SearchPostRequest;
use App\Http\Requests\Post\UpdateRequest as UpdatePostRequest;
use App\Dto\PostDto;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostsController extends Controller
{
    public function __construct(protected PostService $postService) {}

    #[Route('/api/posts/search', methods: ['GET'], name: 'search_posts')]
    #[OA\Get(
        path: '/api/posts/search',
        summary: 'Search posts',
        description: 'Search posts based on query parameters',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Media type expected by the client',
                schema: new OA\Schema(type: 'string', example: 'application/json')
            ),
            new OA\Parameter(
                name: 'query',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string'),
                description: 'Search term for the posts'
            ),
            new OA\Parameter(
                name: 'rowsPerPage',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer'),
                description: 'Number of rows per page'
            ),
            new OA\Parameter(
                name: 'sortBy',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['id', 'created_at', 'published_at']
                ),
                description: 'Sort by fields (id, created_at, published_at)'
            ),
            new OA\Parameter(
                name: 'sortDirection',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['asc', 'desc']
                ),
                description: 'Sort direction (asc or desc)'
            ),
            new OA\Parameter(
                name: 'paginate',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean'),
                description: 'Flag to paginate the results'
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['DRAFT', 'PUBLISHED'] // Assuming PostService::$statuses contains status options
                ),
                description: 'Filter by post status'
            ),
            new OA\Parameter(
                name: 'published_at (start_date)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date'),
                description: 'Filter by published start date'
            ),
            new OA\Parameter(
                name: 'published_at (end_date)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date'),
                description: 'Filter by published end date'
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of posts matching the search criteria',
            ),
            new OA\Response(response: 422, description: 'Invalid input'),
            new OA\Response(
                response: 401,
                description: 'Unauthorize - User does not exists',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'The following user does not exists.')
                    ]
                )
            ),
        ],
        security: [
            'sanctum' => [
                'bearerAuth' => []
            ]
        ]
    )]
    public function searchPosts(SearchPostRequest $request)
    {
        return $this->postService->searchPosts(
            user: $request->user(),
            payload: $request->safe()->all()
        );
    }

    #[Route('/api/posts', methods: ['GET'], name: 'get_user_posts')]
    #[OA\Get(
        path: '/api/posts',
        summary: 'Retrieve current user posts',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Media type expected by the client',
                schema: new OA\Schema(type: 'string', example: 'application/json')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current user posts retrieved successfully',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'user_id', type: 'integer', example: 2),
                            new OA\Property(property: 'title', type: 'string', example: 'Velit id tempore autem quasi qui.'),
                            new OA\Property(property: 'banner_image_url', type: 'string', example: 'https://via.placeholder.com/800x400.png/0022ff?text=business+nostrum'),
                            new OA\Property(property: 'body', type: 'string', example: 'Officiis qui eveniet nesciunt reprehenderit natus dicta molestiae voluptatum.'),
                            new OA\Property(property: 'published_at', type: 'string', format: 'date-time', example: '2024-09-15 04:44:06'),
                            new OA\Property(property: 'status', type: 'string', example: 'PUBLISHED'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-09-15 04:44:06'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-09-15 04:44:06'),
                            new OA\Property(property: 'deleted_at', type: 'string', nullable: true, example: null)
                        ]
                    )
                )
            )
        ],
        security: [
            'sanctum' => [
                'bearerAuth' => []
            ]
        ]
    )]
    public function selfPosts(Request $request)
    {
        $payload = $request->validate([
            'limit' => [
                'numeric',
            ],
        ]);

        return $this->postService->selfPosts($request->user(), $payload);
    }

    #[Route('/api/posts/all', methods: ['GET'], name: 'get_all_posts')]
    #[OA\Get(
        path: '/api/posts/all',
        summary: 'Retrieve all posts (admin role only)',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Media type expected by the client',
                schema: new OA\Schema(type: 'string', example: 'application/json')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All posts retrieved successfully',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'user_id', type: 'integer', example: 2),
                            new OA\Property(property: 'title', type: 'string', example: 'Velit id tempore autem quasi qui.'),
                            new OA\Property(property: 'banner_image_url', type: 'string', example: 'https://via.placeholder.com/800x400.png/0022ff?text=business+nostrum'),
                            new OA\Property(property: 'body', type: 'string', example: 'Officiis qui eveniet nesciunt reprehenderit natus dicta molestiae voluptatum.'),
                            new OA\Property(property: 'published_at', type: 'string', format: 'date-time', example: '2024-09-15 04:44:06'),
                            new OA\Property(property: 'status', type: 'string', example: 'PUBLISHED'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-09-15 04:44:06'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-09-15 04:44:06'),
                            new OA\Property(property: 'deleted_at', type: 'string', nullable: true, example: null)
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorize - User does not exists',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'The following user does not exists.')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - User does not have the required role',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'You do not have the required role to access this resource.')
                    ]
                )
            ),
        ],
        security: [
            'sanctum' => [
                'bearerAuth' => []
            ]
        ]
    )]
    public function allPosts(Request $request)
    {
        // Move this to form request
        $user = $request->user();

        if (!$user || $user->role->name !== 'admin') {
            throw new UnauthorizedException("Unauthorize access error.", 401);
        }

        $payload = $request->validate([
            'rowsPerPage' => [
                'numeric',
            ],
            'sortBy' => [
                'string',
            ],
            'sortDirection' => [
                'string',
                'in:desc,asc',
            ]
        ]);

        return $this->postService->allPosts($request->user(), $payload);
    }

    #[Route('/api/posts/create', methods: ['POST'], name: 'create_post')]
    #[OA\Post(
        path: '/api/posts/create',
        summary: 'Create a new post for the current user',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Media type expected by the client',
                schema: new OA\Schema(type: 'string', example: 'application/json')
            )
        ],
        requestBody: new OA\RequestBody(
            description: 'Post details',
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'My New Post'),
                    new OA\Property(property: 'body', type: 'string', example: 'This is the content of my post.'),
                    new OA\Property(property: 'banner_image_url', type: 'string', nullable: true, example: 'https://...'),
                    new OA\Property(property: 'status', type: 'string', example: 'DRAFT'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Returns the Post Model Object'),
            new OA\Response(response: 422, description: 'Post Create Validation Error'),
        ],
        security: [
            'sanctum' => [
                'bearerAuth' => []
            ]
        ]
    )]
    public function createPost(CreatePostRequest $request)
    {
        $payload = PostDto::fromRequest($request);

        return $this->postService->createPost($request->user(), $payload);
    }

    #[Route('/api/posts/{post}/archive', methods: ['POST'], name: 'archive_post')]
    #[OA\Post(
        path: '/api/posts/{post}/archive',
        summary: 'Archive a post by ID',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'post',
                in: 'path',
                required: true,
                description: 'ID of the post to be archived',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Media type expected by the client',
                schema: new OA\Schema(type: 'string', example: 'application/json')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Post archived successfully'),
            new OA\Response(response: 404, description: 'No query results for model Post'),
        ],
        security: [
            'sanctum' => [
                'bearerAuth' => []
            ]
        ]
    )]
    public function archivePost(Post $post)
    {
        if (!is_null($post->deleted_at)) return $post;

        $post->delete();

        return $post;
    }

    #[Route('/api/posts/{post}/restore', methods: ['POST'], name: 'restore_post')]
    #[OA\Post(
        path: '/api/posts/{post}/restore',
        summary: 'Restore an archived post by ID',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'post',
                in: 'path',
                required: true,
                description: 'ID of the post to be restored',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Media type expected by the client',
                schema: new OA\Schema(type: 'string', example: 'application/json')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Post restored successfully')
        ],
        security: [
            'sanctum' => [
                'bearerAuth' => []
            ]
        ]
    )]
    public function restorePost($post)
    {
        $post = Post::withTrashed()->findOrFail($post);

        if (is_null($post->deleted_at)) return $post;

        $post->restore();

        return $post;
    }

    #[Route('/api/posts/{post}/find', methods: ['GET'], name: 'find_post')]
    #[OA\Get(
        path: '/api/posts/{post}/find',
        summary: 'Find a post by ID',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'post',
                in: 'path',
                required: true,
                description: 'ID of the post to find',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Media type expected by the client',
                schema: new OA\Schema(type: 'string', example: 'application/json')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post retrieved successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'user_id', type: 'integer', example: 2),
                        new OA\Property(property: 'title', type: 'string', example: 'Velit id tempore autem quasi qui.'),
                        new OA\Property(property: 'banner_image_url', type: 'string', example: 'https://via.placeholder.com/800x400.png/0022ff?text=business+nostrum'),
                        new OA\Property(property: 'body', type: 'string', example: 'Officiis qui eveniet nesciunt reprehenderit natus dicta molestiae voluptatum.'),
                        new OA\Property(property: 'published_at', type: 'string', format: 'date-time', example: '2024-09-15 04:44:06'),
                        new OA\Property(property: 'status', type: 'string', example: 'PUBLISHED'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-09-15 04:44:06'),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-09-15 04:44:06'),
                        new OA\Property(property: 'deleted_at', type: 'string', nullable: true, example: null)
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 404,
                description: 'Post not found'
            )
        ],
        security: [
            'sanctum' => [
                'bearerAuth' => []
            ]
        ]
    )]
    public function findPost(Post $post)
    {
        return $this->postService->findPost($post);
    }

    #[Route('/api/posts/{post}/update', methods: ['POST'], name: 'update_post')]
    #[OA\Post(
        path: '/api/posts/{post}/update',
        summary: 'Update a post by ID',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'post',
                in: 'path',
                required: true,
                description: 'ID of the post to update',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Media type expected by the client',
                schema: new OA\Schema(type: 'string', example: 'application/json')
            )
        ],
        requestBody: new OA\RequestBody(
            description: 'Post update details',
            required: true,
            content: new OA\JsonContent(

                type: 'object',
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'My Post Title'),
                    new OA\Property(property: 'body', type: 'string', example: 'My Post Body'),
                    new OA\Property(property: 'status', type: 'string', enum: ['DRAFT', 'PUBLISHED'], example: 'DRAFT'),
                    new OA\Property(property: 'banner_image_url', type: 'string', nullable: true, example: 'https://...')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Post status updated successfully'),
            new OA\Response(response: 404, description: 'Post model not found'),
            new OA\Response(response: 422, description: 'Validation Content Error'),
        ],
        security: [
            'sanctum' => [
                'bearerAuth' => []
            ]
        ]
    )]
    public function updatePost(Post $post, UpdatePostRequest $request)
    {
        return $this->postService->updatePost($post, $request->validated());
    }
}
