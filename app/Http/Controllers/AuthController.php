<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function register(Request $request){
         $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|unique:users|email:dns',
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password)
        ]);

        // $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => $user,
            // 'access_token' => $token,
        ]);
    }

    public function login(Request $request, User $user){
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if(!Auth::attempt($credentials)){
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
            
        }

        $user = User::where('username', $request->username)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;
         return response()->json([
            'message' => 'Login success',
            'data' => $user,
            'access_token' => $token,
        ]);

    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'logout success'
        ]);
    }

   public function forgotEmail(Request $request){
        try{
            $credentials = $request->validate([
                'email' => 'required|string|email:dns',
            ]);
            // echo $credentials;
            $user = User::where('email', $credentials['email'])->get();

            if(count($user) == 0){
                return response()->json([
                    'message' => 'User is empty!',
                    'status' => 400
                ], 400);
            }
            $email = $user[0];
            return response()->json([
                'message' => 'User is exist!',
                'status' => 200,
                'email' => $email['email']
            ]);
        }catch(\Exception $e){
            return response()->json(['success'=>false, 'message'=>$e->getMessage()], 400);
        }
   }

   public function resetPassword(Request $request){
        try{
            $credentials = $request->validate([
                'password' => 'required|min:8|confirmed',
                'password_confirmation' => 'min:8',
                'email' => 'required'
            ]);

            User::where('email', $credentials['email'])->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json(['message' => 'success']);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()], 400);
        }


   }
}
