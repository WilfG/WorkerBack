<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // var_dump($request->all());die;
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone_number' => 'required|string',
            'user_type' => 'required|in:CLIENT,ARTISAN',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }
        // Check if the user already exists
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json([
                'message' => 'User already exists'
            ], 409);
        }
        // Check if the phone number already exists
        $existingPhoneNumber = User::where('phone_number', $request->phone_number)->first();
        if ($existingPhoneNumber) {
            return response()->json([
                'message' => 'Phone number already exists'
            ], 409);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'user_type' => $request->user_type,
        ]);

        $user->notify(new VerifyEmailNotification);

        Log::info('User created', ['user_id' => $user->id]); // Debug log

        // Generate a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;
        // Return the token and user information
        return response()->json([
            'token' => $token,
            'user' => $user
        ], 201);
        // Return a success response
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $user = User::find($request->id);

        if (!$user || !hash_equals(sha1($user->getEmailForVerification()), $request->hash)) {
            return response()->json(['message' => 'Lien de vérification invalide'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email déjà vérifié'], 400);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email vérifié avec succès']);
    }

    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email déjà vérifié'], 400);
        }

        $user->notify(new VerifyEmailNotification);

        return response()->json(['message' => 'Email de vérification renvoyé']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
