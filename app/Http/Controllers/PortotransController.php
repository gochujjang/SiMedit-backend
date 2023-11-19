<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeditResource;
use App\Models\Portofolio;
use App\Models\Portotrans;
use Illuminate\Http\Request;

class PortotransController extends Controller
{

    public function getPortofolio(Request $request){

        $id = $request->user();
        $data = Portofolio::select("id", "title")->where('user_id', $id['id'])->get();
        return new MeditResource(
            true, 
            200, 
            "Success", 
            $data
        );
        
    }
    public function store(Request $request){
        try{
           $validated = $request->validate([
                'nominal' => 'required',
                'porto_id' => 'required',
                'status' => 'required'
            ]);

            // $id = $request->user();
            // $validated['user_id'] = $id;

            $data = Portotrans::create($validated);
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
            ], 500);
        }
    }
}
