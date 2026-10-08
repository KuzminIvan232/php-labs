<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Lab 5: account/role administration — Admin only (enforced by the 'role:admin'
 * middleware on these routes in routes/web.php). Accounts themselves are
 * created via the public POST /auth/register (always as 'client'); this
 * controller is how an Admin promotes someone to Manager/Admin afterwards.
 */
class UserController extends Controller
{
    public function getUsers(): mixed
    {
        return response()->json(['data' => User::all()], Response::HTTP_OK);
    }

    public function getUserItem(string $id): mixed
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['data' => ['error' => 'Not found user by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $user], Response::HTTP_OK);
    }

    /**
     * Changes a user's role, e.g. {"role": "manager"}.
     */
    public function updateUserRole(string $id, Request $request): mixed
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['data' => ['error' => 'Not found user by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);
        $role = $requestData['role'] ?? null;

        if (!in_array($role, User::ROLES, true)) {
            return response()->json(['data' => ['error' => 'role must be one of: ' . implode(', ', User::ROLES)]], Response::HTTP_BAD_REQUEST);
        }

        $user->role = $role;
        $user->save();

        return response()->json(['data' => $user], Response::HTTP_OK);
    }

    public function deleteUser(string $id): mixed
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['data' => ['error' => 'Not found user by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $user->delete();

        return response()->json(['data' => ['message' => 'User ' . $id . ' deleted']], Response::HTTP_OK);
    }
}
