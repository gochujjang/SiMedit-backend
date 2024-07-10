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
            $validatedData = $request->validate([
                'nominal' => 'required',
                'porto_id' => 'required',
                'keterangan' => 'required',
                'status' => 'required',
            ]);

            $porto_id = $validatedData['porto_id'];

            $porto_terkumpul = Portofolio::where('id', $porto_id)->pluck('terkumpul');
            $porto_target = Portofolio::where('id', $porto_id)->pluck('target');

            
            if($validatedData['status'] == 'pemasukan'){
                $persentase = (($porto_terkumpul[0] + (int)$validatedData['nominal']) / $porto_target[0]) * 100;
                Portotrans::create($validatedData);
                Portofolio::where('id', $porto_id)->update(['terkumpul' => $porto_terkumpul[0] + (int)$validatedData['nominal'], 'persentase' => $persentase]);
            }else{
                if($porto_terkumpul[0] < (int)$validatedData['nominal']){
                     return response()->json([
                        'message' => 'Uang yang sudah terkumpul kurang!',
                        'status' => 400
                    ], 400);
                }else{
                    $persentase = (($porto_terkumpul[0] - (int)$validatedData['nominal']) / $porto_target[0]) * 100;
                    Portotrans::create($validatedData);
                    Portofolio::where('id', $porto_id)->update(['terkumpul' => $porto_terkumpul[0] - (int)$validatedData['nominal'], 'persentase' => $persentase]);
                }
            }
            // return response()->json();
            return new MeditResource(true, 200, "success", $validatedData);
        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }
}
