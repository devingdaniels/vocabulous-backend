<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Log the response (for debugging)
            logger('Google User:', (array) $googleUser);

            // Process user authentication
            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]
            );

            // Generate Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            $cookie = cookie(
                'auth_token',
                $token,
                60 * 24 * 7, // 7 days
                '/',
                '.vocabulous.xyz',
                true, // Secure
                true, // HttpOnly
                false, // Raw
                'Strict' // SameSite policy
            );

            return response()->json([
                'user' => $user,
                'token' => $token,
            ])->cookie($cookie);
        } catch (\Exception $e) {
            logger('Error during Google OAuth callback:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
