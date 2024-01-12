<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeditResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{

    public function UpdateProfile(Request $request){
        try{
            $validateData = $request->validate([
                'name' => 'required',
                'email' => 'required',
                'username' => 'required'
            ]);

            $id = $request->user();

            User::where('id',$id['id'])->update($validateData);

            return new MeditResource(true, 200, "success", $validateData);
        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }
    public function ResetPassword(Request $request){
        try{
            $validateData = $request->validate([
                'password' => 'required|min:8|confirmed',
                'password_confirmation' => 'min:8',
                'current_password' => 'required'
            ]);

            $current = $validateData['current_password'];
            $user = $request->user();
            if (!Hash::check($current, $user['password'])) {
                return response()->json(['err' => 'Password Lama Salah!']);
            }

            User::where('id', $user['id'])->update([
                'password' => Hash::make($request->password)
            ]);
            
            return response()->json([
                'msg' => 'Reset Password Success'
            ]);
      
        }catch(\Exception $e){
            return response()->json(['err' => $e, 'msg' => $e->getMessage()]);
        }
    }
}
