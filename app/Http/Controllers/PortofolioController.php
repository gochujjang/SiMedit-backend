<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeditResource;
use App\Models\Portofolio;
use App\Models\PortoMember;
use App\Models\Portotrans;
use Illuminate\Http\Request;

use function PHPSTORM_META\map;

class PortofolioController extends Controller
{
    public function index(Request $request)
{
    try {
        $user_id = $request->user()->id;

        // Ambil semua portofolio yang dimiliki oleh user atau diundang sebagai anggota
        // $portofolios = Portofolio::where('user_id', $user_id)
        //                 ->orWhereHas('members', function ($query) use ($user_id) {
        //                     $query->where('users.id', $user_id);
        //                 })
        //                 ->latest()
        //                 ->get();

        // $portofolios = Portofolio::where([['porto_members.portofolio_id', '=', 'portofolios.id'], ['porto_members.user_id', '=', 'users.id'], ['users.id', '=', $user_id]])->latest();

       $portofolios = Portofolio::select('*')
        ->join('porto_members', 'porto_members.portofolio_id', '=', 'portofolios.id')
        ->join('users', 'porto_members.user_id', '=', 'users.id')
        ->where('users.id', $user_id)
        ->get();

        return new MeditResource(true, 200, "Success", $portofolios);
    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
            'status' => 400
        ], 400);
    }
}


    public function detail(Request $request, $id) {
        try {
            $data = Portofolio::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('transaksi_porto')
            ->first();

            if (!$data) {
                return response()->json([
                    'message' => 'Portfolio not found or access denied',
                    'status' => 404
                ], 404);
            }

    
            return new MeditResource(
                true, 
                200, 
                "Success", 
                $data
            );
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }

    public function store(Request $request){
        try{
             $validated = $request->validate([
                'title' => 'required|max:255',
                'target' => 'required',
            ]);

            $id = $request->user();
            $validated['user_id'] = $id['id'];
            $validated['terkumpul'] = 0; 



            $data = Portofolio::create($validated);

            $porto_id = $data->id;

            PortoMember::create([
                'user_id'=>$id['id'],
                'portofolio_id'=>$porto_id,
                'status'=>'owner'
            ]);


            return new MeditResource(
                true, 
                200, 
                "Success", 
                $data
            );

        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'dd' => $porto_id,
                'status' => 400
            ], 400);
        }
    }

    public function TotalTarget(Request $request){



        try {
            $id = $request->user();
            $totaltarget = Portofolio::select(Portofolio::raw('sum(portofolios.target) as total_target'))
                ->join('porto_members', 'porto_members.portofolio_id', '=', 'portofolios.id')
                ->join('users', 'porto_members.user_id', '=', 'users.id')
                ->where('users.id', $id['id'])
                ->first();
            return new MeditResource(
                true,
                200,
                "Success",
                $totaltarget
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }
    public function TotalTerkumpul(Request $request){
        try {
            $id = $request->user();
            // $total_target = Portofolio::where('user_id', $id['id'])->sum('terkumpul');
            $totalTerkumpul = Portofolio::select(Portofolio::raw('sum(portofolios.terkumpul) as total_terkumpul'))
                ->join('porto_members', 'porto_members.portofolio_id', '=', 'portofolios.id')
                ->join('users', 'porto_members.user_id', '=', 'users.id')
                ->where('users.id', $id['id'])
                ->first();

            return new MeditResource(
                true,
                200,
                "Success",
                $totalTerkumpul
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
        // return response()->json($total_target);
    }

    public function getPortoDetail($id, Request $request){
        try{
            $portoData = Portofolio::with('transaksi_porto.user')->where('id', $id)->first();
            $portoId = $portoData->id;

            $id = $request->user();
            $portoMemberId = PortoMember::where([['user_id','=',$id['id']],['portofolio_id','=', $portoId]])->pluck('id');
            $portoData['porto_member_id'] = $portoMemberId[0];
            return new MeditResource(
                true,
                200,
                "Success",
                $portoData
            );
            
        }catch(\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }

//     public function inviteUser(Request $request, $portfolio_id)
// {
//     try {
//         $validated = $request->validate([
//             'user_id' => 'required|exists:users,id',
//         ]);

//         $portfolio = Portofolio::findOrFail($portfolio_id);

//         // Check if the authenticated user owns the portfolio
//         if ($portfolio->user_id !== $request->user()->id) {
//             return response()->json([
//                 'message' => 'Access denied',
//                 'status' => 403
//             ], 403);
//         }

//         // Add user to the portfolio
//         $portfolio->members()->attach($validated['user_id']);

//         // Update PortoMember
//         PortoMember::create([
//             'portofolio_id' => $portfolio_id,
//             'user_id' => $validated['user_id']
//         ]);

//         return new MeditResource(true, 200, "User invited successfully", $portfolio->members);
//     } catch (\Exception $e) {
//         return response()->json([
//             'message' => $e->getMessage(),
//             'status' => 400
//         ], 400);
//     }
// }



//     public function getMembers($portfolio_id)
//     {
//         try {
//             $portfolio = Portofolio::with('members')->findOrFail($portfolio_id);

//             // Check if the authenticated user is a member of the portfolio
//             if (!$portfolio->members->contains('id', auth()->user()->id)) {
//                 return response()->json([
//                     'message' => 'Access denied',
//                     'status' => 403
//                 ], 403);
//             }

//             return new MeditResource(true, 200, "Success", $portfolio->members);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'message' => $e->getMessage(),
//                 'status' => 400
//             ], 400);
//         }
//     }


    // public function PortofolioTransaction(Request $request){
    //     try{
    //         $validatedData = $request->validate([
    //             'nominal' => 'required',
    //             'porto_id' => 'required',
    //             'status' => 'required',
    //         ]);

    //         // Portotrans::create($validatedData);

    //         $porto_id = $validatedData['porto_id'];

    //         return new MeditResource(true, 200, "success", $porto_id);
    //     }catch(\Exception $e){
    //         return response()->json([
    //             'message' => $e->getMessage(),
    //             'status' => 400
    //         ], 400);
    //     }
    // }

}
