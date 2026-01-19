<?php

namespace Iqonic\FileManager\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Iqonic\FileManager\Models\File;

class AuthApiController extends Controller
{
    /**
     * Issue an API token for the authenticated user
     * Validates credentials if passed, or uses current session user
     */
    public function issueToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        
        // Ensure the User model uses the HasApiTokens trait
        if (!method_exists($user, 'createToken')) {
            return response()->json(['message' => 'User model does not support API tokens. Please add HasApiTokens trait.'], 500);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * Revoke current access token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get authenticated user info
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
