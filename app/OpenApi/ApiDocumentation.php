<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="IdeaBridge API",
 *     version="1.0.0",
 *     description="Complete API documentation for authentication, ideas, and categories endpoints."
 * )
 *
 * @OA\Tag(name="Auth", description="Authentication endpoints")
 * @OA\Tag(name="Ideas", description="Ideas endpoints")
 * @OA\Tag(name="Categories", description="Categories endpoints")
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Use a Sanctum token as: Bearer {token}"
 * )
 *
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=12),
 *     @OA\Property(property="username", type="string", example="john_dev"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="roles", type="array", @OA\Items(type="string", example="Developer")),
 *     @OA\Property(property="permissions", type="array", @OA\Items(type="string", example="add idea")),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-04-22T10:15:35Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-04-22T10:15:35Z")
 * )
 *
 * @OA\Schema(
 *     schema="CategoryResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=3),
 *     @OA\Property(property="name", type="string", example="Frontend"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-04-22T10:15:35Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-04-22T10:15:35Z")
 * )
 *
 * @OA\Schema(
 *     schema="IdeaResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=45),
 *     @OA\Property(property="user_id", type="integer", example=12),
 *     @OA\Property(property="user", ref="#/components/schemas/UserResource"),
 *     @OA\Property(property="taken_by_user_id", type="integer", nullable=true, example=null),
 *     @OA\Property(property="taken_by_user", ref="#/components/schemas/UserResource", nullable=true),
 *     @OA\Property(property="category_id", type="integer", example=3),
 *     @OA\Property(property="category", ref="#/components/schemas/CategoryResource"),
 *     @OA\Property(property="title", type="string", example="Add dark mode toggle"),
 *     @OA\Property(property="description", type="string", example="Implement user-selectable dark mode for the UI."),
 *     @OA\Property(property="status", type="string", enum={"open", "in_progress", "completed", "cancelled"}, example="open"),
 *     @OA\Property(property="is_taken", type="boolean", example=false),
 *     @OA\Property(property="can_take", type="boolean", example=true),
 *     @OA\Property(property="can_leave", type="boolean", example=false),
 *     @OA\Property(property="can_complete", type="boolean", example=false),
 *     @OA\Property(property="upvotes_count", type="integer", example=25),
 *     @OA\Property(property="downvotes_count", type="integer", example=3),
 *     @OA\Property(property="user_vote", type="string", enum={"upvote", "downvote", "neutral"}, example="upvote"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-04-22T10:15:35Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-04-22T10:15:35Z")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorEnvelope",
 *     type="object",
 *     @OA\Property(property="data", nullable=true, example=null),
 *     @OA\Property(
 *         property="error",
 *         type="object",
 *         @OA\Property(property="title", type="string", example="Validation Error"),
 *         @OA\Property(property="message", type="string", example="The given data was invalid."),
 *         @OA\Property(property="details", type="object", additionalProperties=true)
 *     ),
 *     @OA\Property(property="meta", type="object", example={})
 * )
 *
 * @OA\Schema(
 *     schema="AuthErrorEnvelope",
 *     type="object",
 *     @OA\Property(property="data", nullable=true, example=null),
 *     @OA\Property(
 *         property="error",
 *         type="object",
 *         @OA\Property(property="title", type="string", example="Authentication Error"),
 *         @OA\Property(property="message", type="string", example="Unauthenticated."),
 *         @OA\Property(property="details", type="object", example={})
 *     ),
 *     @OA\Property(property="meta", type="object", example={})
 * )
 *
 * @OA\Schema(
 *     schema="AuthorizationErrorEnvelope",
 *     type="object",
 *     @OA\Property(property="data", nullable=true, example=null),
 *     @OA\Property(
 *         property="error",
 *         type="object",
 *         @OA\Property(property="title", type="string", example="Authorization Error"),
 *         @OA\Property(property="message", type="string", example="This action is unauthorized."),
 *         @OA\Property(property="details", type="object", example={})
 *     ),
 *     @OA\Property(property="meta", type="object", example={})
 * )
 *
 * @OA\Schema(
 *     schema="ServerErrorEnvelope",
 *     type="object",
 *     @OA\Property(property="data", nullable=true, example=null),
 *     @OA\Property(
 *         property="error",
 *         type="object",
 *         @OA\Property(property="title", type="string", example="Server Error"),
 *         @OA\Property(property="message", type="string", example="An unexpected error occurred."),
 *         @OA\Property(
 *             property="details",
 *             type="object",
 *             @OA\Property(property="exception", type="string", example="RuntimeException")
 *         )
 *     ),
 *     @OA\Property(property="meta", type="object", example={})
 * )
 *
 * @OA\Post(
 *     path="/api/auth/register",
 *     tags={"Auth"},
 *     summary="Register a new user",
 *     requestBody=@OA\RequestBody(
 *         request="RegisterRequestBody",
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"email", "username", "password", "role"},
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="username", type="string", minLength=3, maxLength=50, example="john_dev"),
 *             @OA\Property(property="password", type="string", minLength=8, example="Password123"),
 *             @OA\Property(property="role", type="string", enum={"user", "developer"}, example="developer")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User registered",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="message", type="string", example="Registered successfully."),
 *                 @OA\Property(property="user", ref="#/components/schemas/UserResource")
 *             ),
 *             @OA\Property(property="error", nullable=true, example=null),
 *             @OA\Property(property="meta", type="object", example={})
 *         )
 *     ),
 *     @OA\Response(response=422, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/ValidationErrorEnvelope")),
 *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ServerErrorEnvelope"))
 * )
 *
 * @OA\Post(
 *     path="/api/auth/login",
 *     tags={"Auth"},
 *     summary="Login with email and password",
 *     requestBody=@OA\RequestBody(
 *         request="LoginRequestBody",
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="password", type="string", example="Password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User logged in",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="message", type="string", example="Logged in successfully."),
 *                 @OA\Property(property="user", ref="#/components/schemas/UserResource")
 *             ),
 *             @OA\Property(property="error", nullable=true, example=null),
 *             @OA\Property(property="meta", type="object", example={})
 *         )
 *     ),
 *     @OA\Response(response=422, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/ValidationErrorEnvelope")),
 *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ServerErrorEnvelope"))
 * )
 *
 * @OA\Post(
 *     path="/api/auth/logout",
 *     tags={"Auth"},
 *     summary="Logout current authenticated user",
 *     security={{"sanctum": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="User logged out",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="message", type="string", example="Logged out successfully.")
 *             ),
 *             @OA\Property(property="error", nullable=true, example=null),
 *             @OA\Property(property="meta", type="object", example={})
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/AuthErrorEnvelope")),
 *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ServerErrorEnvelope"))
 * )
 *
 * @OA\Get(
 *     path="/api/auth/user",
 *     tags={"Auth"},
 *     summary="Get current authenticated user",
 *     security={{"sanctum": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Authenticated user",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="user", ref="#/components/schemas/UserResource")
 *             ),
 *             @OA\Property(property="error", nullable=true, example=null),
 *             @OA\Property(property="meta", type="object", example={})
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/AuthErrorEnvelope")),
 *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ServerErrorEnvelope"))
 * )
 *
 * @OA\Get(
 *     path="/api/ideas",
 *     tags={"Ideas"},
 *     summary="List ideas with optional filters",
 *     security={{"sanctum": {}}},
 *     @OA\Parameter(name="q", in="query", required=false, description="Search text in title or description", @OA\Schema(type="string", maxLength=255, nullable=true), example="dark mode"),
 *     @OA\Parameter(name="page", in="query", required=false, description="Page number", @OA\Schema(type="integer", minimum=1, nullable=true), example=1),
 *     @OA\Parameter(name="limit", in="query", required=false, description="Items per page", @OA\Schema(type="integer", minimum=1, maximum=100, nullable=true), example=15),
 *     @OA\Parameter(name="sort", in="query", required=false, description="Sort by creation date", @OA\Schema(type="string", enum={"asc", "desc"}, nullable=true), example="desc"),
 *     @OA\Response(
 *         response=200,
 *         description="Ideas list",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="ideas", type="array", @OA\Items(ref="#/components/schemas/IdeaResource"))
 *             ),
 *             @OA\Property(property="error", nullable=true, example=null),
 *             @OA\Property(
 *                 property="meta",
 *                 type="object",
 *                 @OA\Property(
 *                     property="pagination",
 *                     type="object",
 *                     @OA\Property(property="total_count", type="integer", example=50),
 *                     @OA\Property(property="page", type="integer", example=1),
 *                     @OA\Property(property="limit", type="integer", example=15),
 *                     @OA\Property(property="total_pages", type="integer", example=4)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/AuthErrorEnvelope")),
 *     @OA\Response(response=403, description="Missing permission", @OA\JsonContent(ref="#/components/schemas/AuthorizationErrorEnvelope")),
 *     @OA\Response(response=422, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/ValidationErrorEnvelope")),
 *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ServerErrorEnvelope"))
 * )
 *
 * @OA\Get(
 *     path="/api/users/ideas",
 *     tags={"Ideas"},
 *     summary="List ideas owned by the current authenticated user",
 *     security={{"sanctum": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="User ideas list",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="ideas", type="array", @OA\Items(ref="#/components/schemas/IdeaResource"))
 *             ),
 *             @OA\Property(property="error", nullable=true, example=null),
 *             @OA\Property(property="meta", type="object", example={})
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/AuthErrorEnvelope")),
 *     @OA\Response(response=403, description="Missing permission", @OA\JsonContent(ref="#/components/schemas/AuthorizationErrorEnvelope")),
 *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ServerErrorEnvelope"))
 * )
 *
 * @OA\Post(
 *     path="/api/ideas",
 *     tags={"Ideas"},
 *     summary="Create a new idea",
 *     security={{"sanctum": {}}},
 *     requestBody=@OA\RequestBody(
 *         request="CreateIdeaRequestBody",
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"category_id", "title", "description"},
 *             @OA\Property(property="category_id", type="integer", example=3),
 *             @OA\Property(property="title", type="string", maxLength=255, example="Add keyboard shortcuts"),
 *             @OA\Property(property="description", type="string", example="Allow quick navigation between sections with shortcuts.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Idea created",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="idea", ref="#/components/schemas/IdeaResource")
 *             ),
 *             @OA\Property(property="error", nullable=true, example=null),
 *             @OA\Property(property="meta", type="object", example={})
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/AuthErrorEnvelope")),
 *     @OA\Response(response=403, description="Missing permission", @OA\JsonContent(ref="#/components/schemas/AuthorizationErrorEnvelope")),
 *     @OA\Response(response=422, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/ValidationErrorEnvelope")),
 *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ServerErrorEnvelope"))
 * )
 *
 * @OA\Put(
 *     path="/api/ideas/{ideaId}/interactions",
 *     tags={"Ideas"},
 *     summary="Set current user interaction on an idea",
 *     security={{"sanctum": {}}},
 *     @OA\Parameter(name="ideaId", in="path", required=true, @OA\Schema(type="integer", minimum=1), example=45),
 *     requestBody=@OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"state"},
 *             @OA\Property(property="state", type="string", enum={"upvote", "downvote", "neutral"}, example="upvote")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Interaction updated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="interaction",
 *                     type="object",
 *                     @OA\Property(property="idea_id", type="integer", example=45),
 *                     @OA\Property(property="user_id", type="integer", example=12),
 *                     @OA\Property(property="user_vote", type="string", enum={"upvote", "downvote", "neutral"}, example="upvote"),
 *                     @OA\Property(property="upvotes_count", type="integer", example=26),
 *                     @OA\Property(property="downvotes_count", type="integer", example=3)
 *                 )
 *             ),
 *             @OA\Property(property="error", nullable=true, example=null),
 *             @OA\Property(property="meta", type="object", example={})
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/AuthErrorEnvelope")),
 *     @OA\Response(response=404, description="Idea not found", @OA\JsonContent(ref="#/components/schemas/ServerErrorEnvelope")),
 *     @OA\Response(response=422, description="Validation failed", @OA\JsonContent(ref="#/components/schemas/ValidationErrorEnvelope")),
 *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ServerErrorEnvelope"))
 * )
 *
 * @OA\Get(
 *     path="/api/categories",
 *     tags={"Categories"},
 *     summary="List all idea categories",
 *     security={{"sanctum": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Categories list",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="categories", type="array", @OA\Items(ref="#/components/schemas/CategoryResource"))
 *             ),
 *             @OA\Property(property="error", nullable=true, example=null),
 *             @OA\Property(property="meta", type="object", example={})
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/AuthErrorEnvelope")),
 *     @OA\Response(response=403, description="Missing permission", @OA\JsonContent(ref="#/components/schemas/AuthorizationErrorEnvelope")),
 *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ServerErrorEnvelope"))
 * )
 */
class ApiDocumentation
{
}
