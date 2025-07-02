<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserWorkImage;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{



    public function register(Request $request)
    {
        Log::info('Registration request received', ['request' => $request->all()]);
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone_number' => 'required|string',
            'user_type' => 'required|in:CLIENT,WORKER',
            'profession_id' => 'nullable|exists:professions,id',
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

        $type = $request->user_type === 'WORKER' ? 'ARTISAN' : 'CLIENT';
        $role = $request->user_type === 'WORKER' ? 'worker' : 'client';
        $code = rand(100000, 999999); // Generate a random verification code
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'user_type' => $type,
            'role' => $role,
            'profession_id' => $request->profession_id,
            'status' => 0, // Default status is 0 (not verified)
            'verification_code' => $code, // Store the verification code
            'is_verified' => false, // Default is not verified
            'email_verified_at' => null, // Email verification timestamp
        ]);


        $user->notify(new VerifyEmailNotification($code));

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
        Log::info('Email verification request received', ['request' => $request->all()]);
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();
        Log::info('User found for verification', ['user_id' => $user->id ?? null]);
        if (!$user || $user->verification_code !== $request->code) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 422);
        }

        $user->is_verified = true;
        $user->verification_code = null; // Clear the code after verification
        $user->status = 1; // Set status to verified
        $user->email_verified_at = now(); // Set the email verified timestamp
        $user->save();

        return response()->json([
            'message' => 'Email vérifié avec succès.'

        ], 200);
    }

    public function resendVerification(Request $request)
    {
        Log::info('Resend verification request received', ['request' => $request->all()]);
        $validation = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $code = rand(100000, 999999);
        $user->verification_code = $code;
        $user->save();
        $user->notify(new VerifyEmailNotification($code));
        return response()->json(['message' => 'Code renvoyé avec succès.']);
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

    public function completeProfile(Request $request)
    {
        Log::info('Complete profile request received', ['request' => $request->all()]);
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'bio' => 'required|string',
            'certification' => 'nullable|file|mimes:jpeg,png,jpg',
            'images.*' => 'nullable|file|mimes:jpeg,png,jpg',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();
        Log::info('User found for profile completion', ['user_id' => $user->id]);
        // Save description
        $user->description = $request->bio;

        // Save certification file
        if ($request->hasFile('certification')) {
            $certPath = $request->file('certification')->store('certifications', 'public');
            $user->certification = $certPath;
        }

        $user->save();

        // Save work images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $imgPath = $img->store('work_images', 'public');
                UserWorkImage::create([
                    'user_id' => $user->id,
                    'image' => $imgPath,
                ]);
            }
        }

        return response()->json(['message' => 'Profil complété avec succès']);
    }
}
