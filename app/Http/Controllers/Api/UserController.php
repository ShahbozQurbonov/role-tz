<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="User management APIs"
 * )
 */
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     tags={"Users"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     )
     * )
     */
    public function index()
    {
        return response()->json(User::with('roles', 'permissions')->get());
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="User"),
     *             @OA\Property(property="email", type="string", format="email", example="user@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User created successfully")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
        $user->assignRole('user');
        return response()->json($user, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get a specific user",
     *     tags={"Users"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\Response(response=200, description="User details", @OA\JsonContent(ref="#/components/schemas/User"))
     * )
     */
    public function show(User $user)
    {
        return response()->json($user->load('roles', 'permissions'));
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update a user",
     *     tags={"Users"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="User"),
     *             @OA\Property(property="email", type="string", format="email", example="user@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User updated successfully")
     * )
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
        ]);

        $user->update(array_filter($data));

        return response()->json($user);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\Response(response=200, description="User deleted")
     * )
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}/assign-role",
     *     summary="Assign role to a user: admin, user",
     *     tags={"Users"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Role assigned successfully")
     * )
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->assignRole($request->role);

        return response()->json(['message' => "Role assigned successfully"]);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}/give-permission",
     *     summary="Grant permission to a user: create user, edit user, delete user, view user",
     *     tags={"Users"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"permission"},
     *             @OA\Property(property="permission", type="string", example="edit posts")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Permission granted successfully")
     * )
     */
    public function givePermission(Request $request, User $user)
    {
        $request->validate([
            'permission' => 'required|string|exists:permissions,name',
        ]);

        $user->givePermissionTo($request->permission);

        return response()->json(['message' => "Permission granted successfully"]);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}/remove-role/{role}",
     *     summary="Remove a role from a user",
     *     tags={"Users"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Roles: admin, user"
     *     ),
     *     @OA\Response(response=200, description="Deleted")
     * )
     */
    public function removeRole(User $user, $role)
    {
        if (!$user->hasRole($role)) {
            return response()->json(['message' => 'Error'], 400);
        }

        $user->removeRole($role);

        return response()->json(['message' => 'Deleted']);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}/revoke-permission-to/{permission}",
     *     summary="Revoke a permission from a user",
     *     tags={"Users"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Permissions: create user, edit user, delete user, view user"
     *     ),
     *     @OA\Response(response=200, description="Deleted")
     * )
     */
    public function revokePermissionTo(User $user, $permission)
    {
        if (!$user->hasPermissionTo($permission)) {
            return response()->json(['message' => 'Error'], 400);
        }

        $user->revokePermissionTo($permission);

        return response()->json(['message' => 'Deleted']);
    }
}
