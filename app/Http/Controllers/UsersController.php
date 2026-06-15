<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Nette\Utils\Random;
use Illuminate\Validation\Rule;


class UsersController extends Controller
{
    use PasswordValidationRules;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // $checkPermission = $this->checkPermission(auth()->user()->id, 'Voir la liste des utilisateurs');
        // if ($checkPermission == false) {
        //     return redirect()->back()->with('errors', "Vous n'avez pas la permission de voir la liste des utilisateurs.");
        // }

        $users = User::all();
        // $roles = Role::all();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        // $roles = Role::all();
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $checkPermission = $this->checkPermission(auth()->user()->id, 'Créer un utilisateur');
            if ($checkPermission == false) {
                return redirect()->back()->with('errors', "Vous n'avez pas la permission de créer un utilisateur.");
            }


            $validator =  Validator::make($request->all(), [
                'lastname' => ['required', 'string', 'max:255'],
                'firstname' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email'],
                'role_id' => ['required'],
                'phoneNumber' => ['required', 'numeric'],
                'password' => $this->passwordRules(),
                'photo' => 'nullable',
                'photo' => 'file|mimes:jpeg,jpg,png,gif,PNG,JPG,JPEG',
            ]);
            // dd($request->roles);
            if ($validator->fails()) {
                return redirect()->back()->with('errors', $validator->errors());
            }


            if ($request->hasfile('photo')) {
                $path = $request->file('photo')->store('usersPhotos', 'public');
                // dd($path);
                $user = User::create([
                    'lastname' => $request->lastname,
                    'firstname' => $request->firstname,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'phoneNumber' => $request->phoneNumber,
                    'photo' => $path,
                    'role_id' => $request->role_id
                ]);

                // return redirect()->back()->with(['status' => 'Utilisateur créé avec succès.']);
            } else {
                $user = User::create(
                    [
                        'lastname' => $request->lastname,
                        'firstname' => $request->firstname,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'phoneNumber' => $request->phoneNumber,
                        'role_id' => $request->role_id
                    ]
                );
            }
            if ($user) {
                Log::channel('gestion_utilisateur')->info(auth()->user()->lastname . ' ' . auth()->user()->firstname . ' a créé un utilisateur');
                return redirect()->back()->with(['status' => 'Utilisateur créé avec succès.']);
            }
        } catch (\Throwable $th) {
            Log::channel('gestion_utilisateur')->info(auth()->user()->lastname . ' ' . auth()->user()->firstname . ' a essayé de créer un utilisateur sans succès');
            return redirect()->back()->with(['errors' => $th->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
       
            $user = User::find($id);
            return view('users.profile', ['user' => $user]);
        // } else {
        //     abort(500, "Une erreur s'est produite");
        // }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            //code...
            //dd($request->all());
            $checkPermission = $this->checkPermission(auth()->user()->id, "Editer les informations d'un utilisateur");
            if ($checkPermission == false) {
                return redirect()->back()->with('errors', "Vous n'avez pas la permission de modifier les informations d'un utilisateur.");
            }

            $validator =  Validator::make($request->all(), [
                'lastname' => ['nullable', 'string', 'max:255'],
                'firstname' => ['nullable', 'string', 'max:255'],
                'role' => ['nullable', 'string', 'max:11'],
                'email' => ['nullable', 'email'],
                'phoneNumber' => ['nullable', 'numeric'],
                'photo' => 'nullable',
                'photo' => 'file|mimes:jpeg,jpg,png,gif,PNG,JPG,JPEG',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('errors', $validator->errors());
            }

            $user_to_update = DB::table('users')->where('id', $id)->first();

            if ($request->hasfile('photo')) {
                $path = $request->file('photo')->store('usersPhotos', 'public');
                // dd($path);
                $user = DB::table('users')->where('id', $id)->update([
                    'lastname' => $request->lastname,
                    'firstname' => $request->firstname,
                    'email' => $request->email,
                    'phoneNumber' => $request->phoneNumber,
                    'photo' => $path,
                    'role_id' => $request->role

                ]);
                if ($user_to_update->photo) {
                    # code...
                    if (file_exists(storage_path($user_to_update->photo))) {
                        unlink(storage_path($user_to_update->photo));
                    }
                }

                // return redirect()->back()->with(['status' => 'Informations modifiées avec succès.']);
            } else {
                $user = DB::table('users')->where('id', $id)->update(
                    [
                        'lastname' => $request->lastname,
                        'firstname' => $request->firstname,
                        'email' => $request->email,
                        'phoneNumber' => $request->phoneNumber,
                        'role_id' => $request->role
                    ]
                );
            }
            if ($user) {
                 Log::channel('gestion_utilisateur')->info(auth()->user()->lastname . ' ' . auth()->user()->firstname . ' a modifié un utilisateur');
                return redirect()->back()->with(['status' => 'Informations modifiées avec succès.']);
            }
        } catch (\Throwable $th) {
            Log::channel('gestion_utilisateur')->info(auth()->user()->lastname . ' ' . auth()->user()->firstname . ' a essayé de modifier les informations d\'un utilisateur sans succès');
            return redirect()->back()->with(['errors' => $th->getMessage()]);

        }
    }

    /**
     *
     */

    public function passwordUpdateRequest(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('errors', $validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Random::generate(60);
            $user->verification_token = $token;
            $user->save();
            $subject = 'Veuillez vérifier votre email';
            $name = $user->firstname . ' ' . $user->lastname;
            $email = $user->email;
            Mail::send(
                'email.reset-password',
                ['name' => $name, 'token' => $user->verification_token],
                function ($mail) use ($email, $name, $subject) {
                    $mail->from(getenv('MAIL_FROM_ADDRESS'), "HOPITAL St Antoine de Padoue");
                    $mail->to($email, $name);
                    $mail->subject($subject);
                }
            );
        }
        return redirect()->back()->with('status', 'Veuillez consulter votre email pour poursuivre..');
    }

    public function verifyUser($token)
    {
        $check = DB::table('users')->where('verification_token', $token)->first();

        if (!is_null($check)) {
            return redirect()->route('update-password');
        } else {
            abort(403, 'Requête non valide');
        }
    }

    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',

            ],
            'password' => $this->passwordRules(),
        ]);

        if ($validator->fails())
            return redirect()->back()->with('errors', $validator->errors());

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            return view('users.profile', ['user' => auth()->user()]);
        } else {
            return redirect()->back()->with('status', 'Email incorrecte');
        }
    }

    public function updateUserPhoto(Request $request)
    {

        $validator = Validator::make($request->only('path'), [
            'path' => 'required',
            'path.*' => 'file|mimes:jpeg,jpg,png,PNG,JPG,JPEG',
        ]);

        if ($validator->fails())
            return redirect()->back()->with('errors', $validator->errors());


        if ($request->hasfile('path')) {
            //$filename  = $request->file('path')->getClientOriginalName();
            //$path = 'users_photo/' . auth()->user()->firstname . '_' . auth()->user()->lastname . '/';
            //$finalPath = $path . $filename;
            $finalPath = $request->file('path')->store('avatars');

            if ($finalPath) {
                $user = auth()->user();
                $user->photo_profil = $finalPath;
                $user->save();
                return redirect()->back()->with('status', 'Photo de profil modifié avec succès');
            } else {
                return redirect()->back()->with('status', 'Un problème est survenu..');
            }
        } else {
            return redirect()->back()->with('status', 'Photo non prise en charge');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
           

            if ($user) {
                $user->delete();
                Log::channel('gestion_utilisateur')->info(auth()->user()->name . ' a supprimé l\'utilisateur '. $user->lastname . ' '. $user->firstname);
                return redirect()->back()->with('success', 'Utilisateur supprimé avec succes.');
            }
        } catch (\Throwable $th) {
            Log::channel('gestion_utilisateur')->info(auth()->user()->name . ' a essayé de supprimer un utilisateur sans succès');
            return redirect()->back()->with('errors', $th->getMessage());
        }
    }
}
