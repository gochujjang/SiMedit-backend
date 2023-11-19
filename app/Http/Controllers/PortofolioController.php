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

            $data = Portofolio::where("user_id", $id['id'])->latest()->get();
            
            $pemasukan_arr = []; 
            $pengeluaran_arr = [];
            $len = 0;
            for($i = 0; $i < count($data); $i++){
                $datas = $data[$i];
                $pemasukan = Portotrans::select("nominal")->where(['porto_id' => $datas['id'], 'status' => 'pemasukan'])->get();
                
                $len = count($pemasukan);
                $pemasukan = $pemasukan[0];
                $pemasukan_arr = $pemasukan['nominal'];
 
                $pengeluaran = Portotrans::select("nominal")->where(['porto_id' => $datas['id'], 'status' => 'pengeluaran'])->get();
                // $datas['terkumpul'] = "tambahannya";
            }
            return new MeditResource(
                true, 
                200, 
                "Success", 
                count($data)
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
}
