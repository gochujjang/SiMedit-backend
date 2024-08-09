<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeditResource;
use App\Models\Portofolio;
use App\Models\PortoMember;
use App\Models\Portotrans;
use App\Models\User;
use Illuminate\Http\Request;

class PortoMemberController extends Controller
{
    public function deleteMember($portofolio_id, $member_id, Request $request) {
        try {
            $user_id = $request->user()->id;

            $portfolioOwner = PortoMember::where('portofolio_id', $portofolio_id)
                ->where('user_id', $user_id)
                ->where('status', 'owner')
                ->first();

            if (!$portfolioOwner) {
                return response()->json([
                    'message' => 'Access denied or portfolio not found',
                    'status' => 403
                ], 403);
            }

            // Prevent members from deleting themselves
            if ($user_id == $member_id) {
                return response()->json([
                    'message' => 'You cannot delete yourself from the portfolio',
                    'status' => 403
                ], 403);
            }

            
            // Delete the member from PortoMember table
            $member = PortoMember::where('portofolio_id', $portofolio_id)
                ->where('user_id', $member_id)
                ->first();

            if (!$member) {
                return response()->json([
                    'message' => 'Member not found in this portfolio',
                    'status' => 404
                ], 404);
            }

            $totalNominal = Portotrans::where('portomember_id', $portofolio_id)
                ->where('user_id', $member_id)
                ->sum('nominal');

            $member->delete();

            Portotrans::where('portomember_id', $portofolio_id)
                ->where('user_id', $member_id)
                ->delete();

            //update portofolio
            $portofolio = Portofolio::find($portofolio_id);
            $portofolio->terkumpul -= $totalNominal;
            $percentage = ($portofolio->terkumpul / $portofolio->target) * 100;
            $portofolio->persentase = $percentage;
            $portofolio->save();
            
            return new MeditResource(true, 200, "Member deleted successfully", [
                'terkumpul' => $portofolio->terkumpul,
                'persentase' => $portofolio->persentase
            ]);

        } catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }
    
    public function listMember($id, Request $request) {
        try {
            // Fetch members associated with the given portfolio ID
            $members = PortoMember::where('portofolio_id', $id)
                                  ->with('user') // Assuming PortoMember has a 'user' relationship
                                  ->get();

            // Transform the collection to rename the 'user' key to 'user_data'
            $members = $members->map(function ($member) {
                $memberArray = $member->toArray();
                $memberArray['user_data'] = [
                    'id' => $member->user->id,
                    'name' => $member->user->name,
                    'username' => $member->user->username,
                    'email' => $member->user->email
                ];
                unset($memberArray['user']);
                return $memberArray;
            });

            return new MeditResource(
                true,
                200,
                "Success",
                $members
            );
        } catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }

    public function InviteMember(Request $request) {
        try {
            // Validate request data
            $validatedData = $request->validate([
                'email' => 'required|email',
                'porto_id' => 'required|exists:portofolios,id'
            ]);
    
            $email = $validatedData['email'];
            $porto_id = $validatedData['porto_id'];
    
            // Check if the user exists
            $user = User::where('email', $email)->first();
    
            if (!$user) {
                return response()->json([
                    'message' => 'User is not registered',
                    'status' => 404
                ], 404);
            }
    
            // Check if the user is already a member of the portfolio
            $existingMember = PortoMember::where('portofolio_id', $porto_id)
                                        ->where('user_id', $user->id)
                                        ->first();
    
            if ($existingMember) {
                return response()->json([
                    'message' => 'User is already in this saving plan',
                    'status' => 409
                ], 409);
            }
    
            // Add the user to the portfolio as a member
            $input = ['user_id' => $user->id, 'portofolio_id' => $porto_id, 'status' => 'member'];
            PortoMember::create($input);
    
            return new MeditResource(
                true, 
                200, 
                "Friend successfully invited", 
                $input
            );
    
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while inviting the member',
                'error' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }
    
    
    
    // public function InviteMember(Request $request) {
    //     try{
    //         $validatedData = $request->validate([
    //             'email' => 'required|email',
    //             'porto_id' => 'required'
    //         ]);

    //         $email = $validatedData['email'];

    //         $member_id = User::where('email', $email)->pluck('id');
    //         $input = ['user_id' => $member_id[0], 'portofolio_id' => $validatedData['porto_id'], 'status' => 'member'];
    //         PortoMember::create($input);

    //         return new MeditResource(
    //             true, 
    //             200, 
    //             "Success", 
    //             $input
    //         );
    //     }catch(\Exception $e){
    //         return response()->json([
    //             'message' => $e->getMessage(),
    //             'status' => 400
    //         ], 400);
    //     }
    // }
}
