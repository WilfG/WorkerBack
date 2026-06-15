<?php

namespace App\Http\Controllers;

use App\Models\Job;
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
            'country_id' => 'nullable|exists:countries,id',
            'address' => 'nullable|string',
            
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
            'country_id' => $request->country_id,
            'address' => $request->address,
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
        // Get work images (array of URLs)
        $workImages = UserWorkImage::where('user_id', $user->id)->pluck('image')->toArray();

        // Optionally, get completed jobs
        $completedJobs = Job::where('worker_id', $user->id)
            ->where('status', 'completed')
            ->get(['title', 'description']);

        // Add extra fields to user object
        $user->certification = $user->certification; // already present if column exists
        $user->works = $workImages;
        $user->completed_jobs = $completedJobs;
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
        if (!$user || $user->verification_code != $request->code) {
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

        // Get user type from request
        $userType = $request->input('user_type');

        // Base validation rules for all users
        $baseRules = [
            'email' => 'required|email|exists:users,email',
            'user_type' => 'required|in:WORKER,CLIENT',
            'id_card_recto' => 'required|file|mimes:jpeg,png,jpg|max:2048',
            'id_card_verso' => 'required|file|mimes:jpeg,png,jpg|max:2048',
            'profile_photo' => 'required|file|mimes:jpeg,png,jpg|max:2048',
        ];

        // Additional validation rules for workers
        $workerRules = [
            'bio' => 'required|string|min:10|max:1000',
            'reference_name' => 'required|string|max:255',
            'reference_number' => 'required|string|max:30',
            'certification' => 'required|file|mimes:jpeg,png,jpg|max:2048',
            'images.*' => 'required|file|mimes:jpeg,png,jpg|max:2048',
        ];

        // Client validation rules (only base requirements)
        $clientRules = [
            'bio' => 'nullable|string',
            'reference_name' => 'nullable|string',
            'reference_number' => 'nullable|string',
        ];

        // Combine validation rules based on user type
        $validationRules = $baseRules;
        if ($userType === 'WORKER') {
            $validationRules = array_merge($validationRules, $workerRules);
        } else {
            $validationRules = array_merge($validationRules, $clientRules);
        }

        // Validate the request
        $request->validate($validationRules);

        $user = User::where('email', $request->email)->firstOrFail();
        Log::info('User found for profile completion', ['user_id' => $user->id, 'user_type' => $userType]);

        // Save ID card images (required for all users)
        if ($request->hasFile('id_card_recto')) {
            $idRectoPath = $request->file('id_card_recto')->store('id_cards', 'public');
            $user->id_card_recto = $idRectoPath;
        }

        if ($request->hasFile('id_card_verso')) {
            $idVersoPath = $request->file('id_card_verso')->store('id_cards', 'public');
            $user->id_card_verso = $idVersoPath;
        }

        // Save profile photo (required for all users)
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('avatars', 'public');
            $user->profile_photo = $profilePhotoPath;
        }

        // Save worker-specific data only if user is a worker
        if ($userType === 'ARTISAN') {
            // Save description and reference information
            $user->description = $request->bio;
            $user->reference_name = $request->reference_name;
            $user->reference_number = $request->reference_number;

            // Save certification file (optional for workers)
            if ($request->hasFile('certification')) {
                $certPath = $request->file('certification')->store('certifications', 'public');
                $user->certification = $certPath;
            }
        }

        // Mark profile as completed
        $user->profile_completed = true;
        $user->save();

        // Save work images (only for workers)
        if ($userType === 'ARTISAN' && $request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $imgPath = $img->store('work_images', 'public');
                UserWorkImage::create([
                    'user_id' => $user->id,
                    'image' => $imgPath,
                ]);
            }
        }

        Log::info('Profile completed successfully', ['user_id' => $user->id, 'user_type' => $userType]);

        return response()->json([
            'message' => 'Profil complété avec succès',
            'user_type' => $userType,
            'profile_completed' => true
        ]);
    }
    
   public function updateProfile(Request $request)
    {
      $user = User::findOrFail($request->id);
        Log::info('Update profile request received', ['request' => $request->all()]);
        // Log::info('Get the avatar mime type', ['mime_type' => $request->file('avatar')->getMimeType() ?? 'No avatar provided']);
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
             'country_id' => 'nullable|exists:countries,id',
            'profession_id' => 'nullable|exists:professions,id',
            'address' => 'nullable|string|max:255',
            'reference_name' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:30',
            'id_card_recto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'id_card_verso' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'certification' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'images.*' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->description = $request->description;
         $user->country_id = $request->country_id;
        $user->profession_id = $request->profession_id;
        $user->address = $request->address;
        $user->reference_name = $request->reference_name;
        $user->reference_number = $request->reference_number;
        $user->id_card_recto = $request->id_card_recto;
        $user->id_card_verso = $request->id_card_verso;
        $user->certification = $request->certification;
        $user->profile_completed = true; // Ensure profile is marked as completed


        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->profile_photo = '/storage/' . $avatarPath;
            Log::info('Avatar updated', ['user_id' => $user->id, 'avatar_path' => $avatarPath]);
        }

         // Save certification file (optional for workers)
        if ($request->hasFile('certification')) {
            $certPath = $request->file('certification')->store('certifications', 'public');
            $user->certification = $certPath;
        }

        $user->save();
        if ($request->hasFile('images')) {

            foreach ($request->file('images') as $img) {
                $imgPath = $img->store('work_images', 'public');
                UserWorkImage::create([
                    'user_id' => $user->id,
                    'image' => $imgPath,
                ]);
            }
        }
        Log::info('Profile updated successfully', ['user_id' => $user->id]);


        return response()->json(['user' => $user], 200);
    }
}
