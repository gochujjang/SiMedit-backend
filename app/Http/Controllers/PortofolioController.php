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
                'target' => 'required|numeric|max:999999999999999|min:1',
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

    public function delete($id, Request $request) {
        try {
            $user_id = $request->user()->id;

            $portfolioMember = PortoMember::where('portofolio_id', $id)
                ->where('user_id', $user_id)
                ->where('status', 'owner')
                ->first();

            if (!$portfolioMember) {
                return response()->json([
                    'message' => 'Portfolio not found or access denied',
                    'status' => 404
                ], 404);
            }

            // Delete related PortoMember records
            PortoMember::where('portofolio_id', $id)->delete();

            // Delete related Portotrans records
            Portotrans::where('portomember_id', $id)->delete();

            // Delete the portfolio
            Portofolio::where('id', $id)->delete();

            return new MeditResource(true, 200, "Portfolio deleted successfully", null);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }

    public function update($id, Request $request) {
        try {
            $validatedData = $request->validate([
                'title' => 'required|max:255',
                'target' => 'required|numeric|max:999999999999999|min:1'
            ]);

            $user_id = $request->user()->id;

            // Check if the user is an owner of the portfolio
            $portfolioMember = PortoMember::where('portofolio_id', $id)
                ->where('user_id', $user_id)
                ->where('status', 'owner')
                ->first();

            if (!$portfolioMember) {
                return response()->json([
                    'message' => 'Portfolio not found or access denied',
                    'status' => 404
                ], 404);
            }

            // Find the portfolio
            $portfolio = Portofolio::findOrFail($id);
            

            // Update the portfolio with the validated data
            $portfolio->update($validatedData);

            $portfolio->persentase = (int)(($portfolio->terkumpul / $portfolio->target) * 100);
            $portfolio->save();

            // Return a success response with the updated portfolio
            return new MeditResource(true, 200, "Saving updated successfully", $portfolio);
        } catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }

}
