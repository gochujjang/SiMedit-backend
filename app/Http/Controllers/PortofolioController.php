<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeditResource;
use App\Models\Portofolio;
use App\Models\Portotrans;
use Illuminate\Http\Request;

class PortofolioController extends Controller
{
    public function index(Request $request){
        try{
            $id = $request->user();
            $data = Portofolio::where('user_id', $id['id'])->latest()->get();
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

        $id = $request->user();
        $total_target = Portofolio::where('user_id', $id['id'])->sum('target');

        return response()->json($total_target);
    }
    public function TotalTerkumpul(Request $request){

        $id = $request->user();
        $total_target = Portofolio::where('user_id', $id['id'])->sum('terkumpul');

        return response()->json($total_target);
    }


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
