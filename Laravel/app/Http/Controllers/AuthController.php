<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Public self-registration. Always creates a ROLE_CLIENT account —
     * Manager/Admin accounts are provisioned by an Admin via
     * PUT /users/{id}/role (see UserController), never by public signup,
     * so nobody can grant themselves elevated access.
     */
    public function register(Request $request): mixed
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $email = $requestData['email'] ?? null;
        $password = $requestData['password'] ?? null;

        if (!$email || !$password) {
            return response()->json(['data' => ['error' => 'email and password are required']], Response::HTTP_BAD_REQUEST);
        }

        if (User::where('email', $email)->exists()) {
            return response()->json(['data' => ['error' => 'A user with this email already exists']], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->name = $requestData['name'] ?? explode('@', $email)[0];
        $user->email = $email;
        $user->password = $password; // hashed automatically by the 'hashed' cast
        $user->role = User::ROLE_CLIENT; // never trust a client-supplied role
        $user->save();

        return response()->json(['data' => $user], Response::HTTP_CREATED);
    }

    /**
     * Issues a JWT for valid email/password credentials.
     */
    public function login(Request $request): mixed
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $credentials = [
            'email' => $requestData['email'] ?? null,
            'password' => $requestData['password'] ?? null,
        ];

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['data' => ['error' => 'Invalid credentials']], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json(['token' => $token]);
    }

    /**
     * Returns the authenticated user's own account — handy for checking which
     * role a token carries.
     */
    public function me(Request $request): mixed
    {
        return response()->json(['data' => $request->user()]);
    }

    /**
     * Invalidates the current JWT (stateless logout).
     */
    public function logout(): mixed
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['data' => ['message' => 'Logged out']]);
    }
}
