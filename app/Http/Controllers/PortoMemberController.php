<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeditResource;
use App\Models\PortoMember;
use App\Models\User;
use Illuminate\Http\Request;

class PortoMemberController extends Controller
{
    public function InviteMember(Request $request) {
        try{
            $validatedData = $request->validate([
                'email' => 'required',
                'porto_id' => 'required'
            ]);

            $email = $validatedData['email'];

            $member_id = User::where('email', $email)->pluck('id');
            $input = ['user_id' => $member_id[0], 'portofolio_id' => $validatedData['porto_id'], 'status' => 'member'];
            PortoMember::create($input);

            return new MeditResource(
                true, 
                200, 
                "Success", 
                $input
            );
        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }
}
