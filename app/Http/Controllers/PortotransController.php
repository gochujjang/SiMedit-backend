<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeditResource;
use App\Models\Portofolio;
use App\Models\PortoMember;
use App\Models\Portotrans;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
                'portomember_id' => 'required',
                'keterangan' => 'required',
                'status' => 'required',
                'foto' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            // Get the user ID from the request
            $userId = $request->user()->id;
            $userName = $request->user()->name;


            // Add user_id to the validated data
            $validatedData['user_id'] = Auth::user()->id;

            $portomember_id = $validatedData['portomember_id'];

            $porto_id = PortoMember::where('portofolio_id', $portomember_id)->select('portofolio_id', 'user_id')->first();

            $porto_terkumpul = Portofolio::where('id', $porto_id['portofolio_id'])->pluck('terkumpul');
            $porto_target = Portofolio::where('id', $porto_id['portofolio_id'])->pluck('target');


            // $user_data = User::where('id', (int)$porto_id['user_id'])->select('username', 'email')->first();
            $user_data = User::where('id', $userId)->select('username', 'email')->first();

            //upload foto
            if($request->file('foto')){
                $randomString = Str::random(20);
                // Generate a unique file name with user name and description
                $extension = $request->file('foto')->getClientOriginalExtension();
                $filename = $userName . '_' . $randomString . '.' . $extension;
                
                // Ensure the filename is URL-safe
                $filename = preg_replace('/[^A-Za-z0-9\-]/', '_', $filename);

                $path = $request->file('foto')->storeAs('bukti-pembayaran', $filename);
                $validatedData['foto'] = env('APP_URL') . '/storage/' . $path;
            }

            
            if($validatedData['status'] == 'pemasukan'){
                $persentase = (($porto_terkumpul[0] + (int)$validatedData['nominal']) / $porto_target[0]) * 100;
                Portotrans::create($validatedData);
                Portofolio::where('id', $porto_id['portofolio_id'])->update(['terkumpul' => $porto_terkumpul[0] + (int)$validatedData['nominal'], 'persentase' => $persentase]);
            }else{
                if($porto_terkumpul[0] < (int)$validatedData['nominal']){
                     return response()->json([
                        'message' => 'Uang yang sudah terkumpul kurang!',
                        'status' => 400
                    ], 400);
                }else{
                    $persentase = (($porto_terkumpul[0] - (int)$validatedData['nominal']) / $porto_target[0]) * 100;
                    Portotrans::create($validatedData);
                    Portofolio::where('id', $porto_id['portofolio_id'])->update(['terkumpul' => $porto_terkumpul[0] - (int)$validatedData['nominal'], 'persentase' => $persentase]);
                }
            }
            // return response()->json();
            $validatedData['user_Data'] = $user_data;                                                                                
            return new MeditResource(true, 200, "success", $validatedData);
        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }
}
