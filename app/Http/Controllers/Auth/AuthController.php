<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
//use App\Http\Controllers\OTPController;
use Illuminate\Http\Request;
use App\Models\User;
//use App\Models\OTP;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
//use Illuminate\Support\Facades\Mail;
//use App\Mail\SendMail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lastname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'birthday' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'post' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'number' => 'nullable|integer|digits:8|starts_with:5,6,7,01,02,03,05,06,07|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

            $user = User::create([
                'lastname' => $request->lastname,
                'firstname' => $request->firstname,
                'birthday' => $request->birthday,
                'type' => $request->type,
                'post' => $request->post,
                'genre' => $request->genre,
                'number' => $request->number,
                'email' => $request->email,
                'slug' => Str::random(10),
                'isActive' => ($request->type === "livreur") ? false : true,
                'password' => bcrypt($request->password),
            ]);

            if ($request->hasFile('image')) {
                // Générer un nom aléatoire pour l'image
                $imageName = Str::random(10) . '.' . $request->image->getClientOriginalExtension();
    
                // Enregistrer l'image dans le dossier public/images
                $imagePath = $request->image->move(public_path('users'), $imageName);
    
                if ($imagePath) {
                    $user->update([
                        'image' => 'users/' . $imageName,
                    ]);
                }
            }
    
            $token = $user->createToken('my-app-token')->accessToken;
    
            return response(['user' => $user, 'access_token' => $token]);
        
    }

    public function login(Request $request)
    {
        $credentialsEmail = $request->only('email', 'password');
        $credentialsNumero = $request->only('number', 'password');

        if (Auth::attempt($credentialsEmail) || Auth::attempt($credentialsNumero)) {
            $user = Auth::user();
            if(!$user->isActive){
                return response(['errors' => 'Votre compte a été désactivé temporairement'], 401);
            }
            $token = $user->createToken('my-app-token')->accessToken;
            return response(['user' => $user, 'access_token' => $token]);
        }

        return response(['error' => 'Identifiants de connexion invalides'], 401);
    }

    public function editPassword(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }


        $user = User::where('email', $request->email)->first();

        if ($user) {

            $user->update([
                'password' => bcrypt($request->password),
            ]);
    
            return response()->json(['message' => 'Votre mot de passe à bien été modifier, vous pouvez vous connecter maintenant'], 200);
        
        } else {
            // Réponse d'erreur
            return response()->json(['error' => "Votre adresse e-mail ou votre numéro de téléphone est incorrect"], 422);
        }
        
    }

    public function index()
    {
        $users = User::all();
        return response()->json(['users' => $users], 200);
    }

    public function userAuth(Request $request)
    {
        $data = Auth::user();
        
        if (!$data) {
            return response()->json(['message' => 'Donnée non trouvée'], 404);
        }

        return response()->json([
            'users' => $data,
            'message' => "Donnée trouvée"
        ], 200);
    }

    public function userBy(Request $request)
    {
        $data = User::find($request->id);
        
        if (!$data) {
            return response()->json(['message' => 'Donnée non trouvée'], 404);
        }

        return response()->json([
            'users' => $data,
            'message' => "Donnée trouvée"
        ], 200);
    }

    public function update(Request $request)
    {
       
        //$otpController = app(OTPController::class);
        //$msg = $otpController->generateOTP($request);
        //$msg = $otpController->verifyOTP($request);
        
        $validator = Validator::make($request->all(), [
            'lastname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'birthday' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'post' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'number' => 'required|integer|digits:8|starts_with:5,6,7,01,02,03,05,06,07',
            'email' => 'required|string|email|max:255',
            'password' => 'nullable|string|min:8',
            'oldPassword' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $id = Auth::user()->id;
        $user = User::where('id',$id)->first();
        
        // modification de la photo de profile
        if ($request->hasFile('image')) {
            // Générer un nom aléatoire pour l'image
            $imageName = Str::random(10) . '.' . $request->image->getClientOriginalExtension();

            // Enregistrer l'image dans le dossier public/images
            $imagePath = $request->image->move(public_path('users'), $imageName);

            if ($imagePath) {
                // Créer la nouvelle catégorie de média
                Storage::delete($user->image);
                $user->update([
                    'image' => 'users/' . $imageName,
                ]);
            }
        }

        // modification du mot de passe
        if (Hash::check($request->oldPassword, $user->password)) {
            
            $user->update([
                'password' => bcrypt($request->password),
                ]);
        } else {
           
            return response()->json(['errors' => "Échec de la modification ! Votre ancien mot de passe est incorrect"], 422);

        }

        if ($user) {
            $user->update([
                'lastname' => $request->lastname,
                'firstname' => $request->firstname,
                'birthday' => $request->number,
                'type' => $request->number,
                'post' => $request->email,
            ]);
    
            return response(['user' => $user,"message" =>"Les données ont été bien modifiées"]);
        } else {
            // Réponse d'erreur
            return response()->json(['errors' => "Echec ! Les données n'ont pas été modifiées"], 422);
        }
        
    }



    public function disable(Request $request)
    {
        $data = User::find($request->id);
        if (!$data) {
            return response()->json(['message' => 'Donnée non trouvée'], 404);
        }
        $data->update([
            'isActive' => !$data->isActive,
        ]);
        return response()->json([
            'users' => $data,
            'message' => "Donnée modifiée"
        ], 200);
    }



    public function sendEmail($email, $password)
    {
      $data = [
          'subject' => 'Compte EMC',
          'title' => 'Bienvenue sur EMC !',
          'content' => 'Votre compte a bien été créé. Pour vous connecter, 
          utilisez votre email:'.$email.' et le mot de passe: '.$password
      ];

      Mail::to($email)->send(new SendMail($data, null));

      return "E-mail envoyé avec succès.";
    }


}