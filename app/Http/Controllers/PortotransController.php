<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeditResource;
use App\Models\Portofolio;
use App\Models\PortoMember;
use App\Models\Portotrans;
use App\Models\User;
use Brick\Math\BigInteger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PortotransController extends Controller
{

    public function deletePortotrans($portotrans_id, Request $request)
    {
        try {
            $user_id = $request->user()->id;

            // Get the portotrans record
            $portotrans = Portotrans::with('user')->find($portotrans_id);
            if (!$portotrans) {
                return response()->json([
                    'success' => false,
                    'code' => 404,
                    'message' => 'Portotrans not found',
                    'data' => null
                ], 404);
            }

            // Check if the user is the owner or the member who created the portotrans
            $portoMember = PortoMember::where('portofolio_id', $portotrans->portomember_id)
                                      ->where('user_id', $user_id)
                                      ->first();

            if (!$portoMember || ($portoMember->status !== 'owner' && $portotrans->user_id !== $user_id)) {
                return response()->json([
                    'success' => false,
                    'code' => 403,
                    'message' => 'Access denied',
                    'data' => null
                ], 403);
            }

            // Update Portofolio terkumpul and persentase
            $portofolio = Portofolio::find($portotrans->portomember_id);
            if ($portotrans->status === 'pemasukan') {
                $portofolio->terkumpul -= $portotrans->nominal;
            } else {
                $portofolio->terkumpul += $portotrans->nominal;
            }
            $portofolio->persentase = ($portofolio->terkumpul / $portofolio->target) * 100;
            $portofolio->save();

            // Delete the portotrans record
            $portotrans->delete();

            return new MeditResource(true, 200, "Transaction deleted successfully", null);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'code' => 400,
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }
    
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
        try {
            $validatedData = $request->validate([
                'nominal' => 'required|numeric|max:999999999999999|min:1',
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
    
            // Mengambil nilai terkumpul dan target dari portofolio sebagai BigInteger
            $porto_terkumpul = BigInteger::of(Portofolio::where('id', $porto_id['portofolio_id'])->value('terkumpul'));
            $porto_target = BigInteger::of(Portofolio::where('id', $porto_id['portofolio_id'])->value('target'));
    
            $user_data = User::where('id', $userId)->select('username', 'email')->first();
    
            // Upload foto
            if ($request->file('foto')) {
                $randomString = Str::random(20);
                $extension = $request->file('foto')->getClientOriginalExtension();
                $originalName = pathinfo($request->file('foto')->getClientOriginalName(), PATHINFO_FILENAME);
                $sanitizedName = preg_replace('/[^A-Za-z0-9\-]/', '_', $originalName);
                $filename = $userName . '_' . $randomString . '_' . $sanitizedName . '.' . $extension;
    
                $path = $request->file('foto')->storeAs('bukti-pembayaran', $filename);
                $validatedData['foto'] = env('APP_URL') . '/storage/' . $path;
            }
    
            if ($validatedData['status'] == 'pemasukan') {
                $newTerkumpul = $porto_terkumpul->plus($validatedData['nominal']);
                $persentase = (int)($newTerkumpul->toFloat() / $porto_target->toFloat() * 100);
                Portotrans::create($validatedData);
                Portofolio::where('id', $porto_id['portofolio_id'])->update(['terkumpul' => $newTerkumpul, 'persentase' => $persentase]);
            } else {
                if ($porto_terkumpul->isLessThan($validatedData['nominal'])) {
                    return response()->json([
                        'message' => 'Uang yang sudah terkumpul kurang!',
                        'status' => 400
                    ], 400);
                } else {
                    $newTerkumpul = $porto_terkumpul->minus($validatedData['nominal']);
                    $persentase = (int)($newTerkumpul->toFloat() / $porto_target->toFloat() * 100);
                    Portotrans::create($validatedData);
                    Portofolio::where('id', $porto_id['portofolio_id'])->update(['terkumpul' => $newTerkumpul, 'persentase' => $persentase]);
                }
            }
    
            $validatedData['user_Data'] = $user_data;                                                                                
            return new MeditResource(true, 200, "success", $validatedData);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }


    // public function store(Request $request){
    //     try{
    //         $validatedData = $request->validate([
    //             'nominal' => 'required|numeric|max:999999999999999|min:1',
    //             'portomember_id' => 'required',
    //             'keterangan' => 'required',
    //             'status' => 'required',
    //             'foto' => 'required|image|mimes:jpeg,jpg,png|max:2048',
    //         ]);

    //         // Get the user ID from the request
    //         $userId = $request->user()->id;
    //         $userName = $request->user()->name;


    //         // Add user_id to the validated data
    //         $validatedData['user_id'] = Auth::user()->id;

    //         $portomember_id = $validatedData['portomember_id'];

    //         $porto_id = PortoMember::where('portofolio_id', $portomember_id)->select('portofolio_id', 'user_id')->first();

    //         $porto_terkumpul = Portofolio::where('id', $porto_id['portofolio_id'])->pluck('terkumpul');
    //         $porto_target = Portofolio::where('id', $porto_id['portofolio_id'])->pluck('target');


    //         $user_data = User::where('id', $userId)->select('username', 'email')->first();

    //         //upload foto
    //         if($request->file('foto')){
    //             $randomString = Str::random(20);
    //             // Generate a unique file name with user name and description
    //             $extension = $request->file('foto')->getClientOriginalExtension();
    //             $originalName = pathinfo($request->file('foto')->getClientOriginalName(), PATHINFO_FILENAME);
    //             $sanitizedName = preg_replace('/[^A-Za-z0-9\-]/', '_', $originalName);
    //             $filename = $userName . '_' . $randomString . '_' . $sanitizedName . '.' . $extension;

    //             $path = $request->file('foto')->storeAs('bukti-pembayaran', $filename);
    //             $validatedData['foto'] = env('APP_URL') . '/storage/' . $path;
    //         }

            
    //         if($validatedData['status'] == 'pemasukan'){
    //             $persentase = (int)(($porto_terkumpul[0] + $validatedData['nominal']) / $porto_target[0]) * 100;
    //             Portotrans::create($validatedData);
    //             Portofolio::where('id', $porto_id['portofolio_id'])->update(['terkumpul' => $porto_terkumpul[0] + $validatedData['nominal'], 'persentase' => $persentase]);
    //         }else{
    //             if($porto_terkumpul[0] < $validatedData['nominal']){
    //                  return response()->json([
    //                     'message' => 'Uang yang sudah terkumpul kurang!',
    //                     'status' => 400
    //                 ], 400);
    //             }else{
    //                 $persentase = (int)(($porto_terkumpul[0] - $validatedData['nominal']) / $porto_target[0]) * 100;
    //                 Portotrans::create($validatedData);
    //                 Portofolio::where('id', $porto_id['portofolio_id'])->update(['terkumpul' => $porto_terkumpul[0] - $validatedData['nominal'], 'persentase' => $persentase]);
    //             }
    //         }
    //         // return response()->json();
    //         $validatedData['user_Data'] = $user_data;                                                                                
    //         return new MeditResource(true, 200, "success", $validatedData);
    //     }catch(\Exception $e){
    //         return response()->json([
    //             'message' => $e->getMessage(),
    //             'status' => 400
    //         ], 400);
    //     }
    // }
}
