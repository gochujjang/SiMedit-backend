<?php

namespace App\Http\Controllers;

use App\Http\Resources\MeditResource;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    // Semua data transaksi
    public function index(Request $request){
        try{
            $id = $request->user();
            $data = Transaction::where('user_id', $id['id'])->latest()->get();
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

    // Transaksi terakhir home
    public function latestTransaction(Request $request){
        try{
            $id = $request->user();
            $data = Transaction::where('user_id', $id['id'])->latest()->take(5)->get();

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

    // Nambah Transaksi 
    public function store(Request $request){
        try{
            $validated = $request->validate([
                'nominal' => 'required',
                'tgl' => 'required',
                'Keterangan' => 'required',
                'status' => 'required'
            ]);

            $id = $request->user();
            $validated['user_id'] = $id['id'];

            Transaction::create($validated);

            return new MeditResource(
                true,
                200,
                "success",
                $validated
            );


        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }

    }

    // Pemasukkan transaksi
    public function income(Request $request){
        
        try{
            $id = $request->user();
            $income_data = Transaction::where(["status" => "pemasukan", "user_id" => $id['id']])->get();

            $income_arr = array();
            for($i = 0; $i < count($income_data); $i++){
                $income_nominal = $income_data[$i];
                $income_arr[$i] = $income_nominal['nominal'];
            }


            return new MeditResource(
                true,
                200,
                "success",
                array_sum($income_arr)
            );


        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }

// Pengeluaran transaksi
    public function expense(Request $request){
        
        try{
            $id = $request->user();
            $expense_data = Transaction::where(["status" => "pengeluaran", "user_id" => $id['id']])->get();

            $expense_arr = array();
            for($i = 0; $i < count($expense_data); $i++){
                $expense_nominal = $expense_data[$i];
                $expense_arr[$i] = $expense_nominal['nominal'];
            }


            return new MeditResource(
                true,
                200,
                "success",
                array_sum($expense_arr)
            );


        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }

    // Sisa uang transakasi
    public function totalMoney(Request $request){
        try{
            $id = $request->user();
            $income_data = Transaction::where(["status" => "pemasukan", "user_id" => $id['id']])->get();

            $income_arr = array();
            for($i = 0; $i < count($income_data); $i++){
                $income_nominal = $income_data[$i];
                $income_arr[$i] = $income_nominal['nominal'];
            }
            $expense_data = Transaction::where(["status" => "pengeluaran", "user_id" => $id['id']])->get();
            $expense_arr = array();
            for($i = 0; $i < count($expense_data); $i++){
                $expense_nominal = $expense_data[$i];
                $expense_arr[$i] = $expense_nominal['nominal'];
            }

            $data = 0;
            $total_money = array_sum($income_arr) - array_sum($expense_arr); 

            if($total_money > 0) {
                $data = $total_money;
            }

            return new MeditResource(
                true,
                200,
                "success",
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
