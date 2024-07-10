<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortofolioController;
use App\Http\Controllers\PortotransController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/password-email', [AuthController::class, 'forgotEmail']);

// Yang buat lupa
Route::post('/password-reset', [AuthController::class, 'resetPassword']);


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['middleware' => ['auth:sanctum']], function() {
    Route::get('/user', function(Request $request) {
        return $request->user();
    });

    // Reset Password yang di profile
    Route::post('/reset-password', [ProfileController::class, 'ResetPassword']);

    // Update Profile
    Route::post('/update-profile', [ProfileController::class, 'UpdateProfile']);


    // Semua transaksi
    Route::get('/transaction', [TransactionController::class, 'index']);
    // Nambah transaksi
    Route::post('/transaction', [TransactionController::class, 'store']);
    // Transaksi terbaru di home
    Route::get('/latestTransaction', [TransactionController::class, 'latestTransaction']);


    // Pemasukkan Halaman Transaksi
    Route::get('/income', [TransactionController::class, 'income']);
    // Pengeluaran Halaman Transaksi
    Route::get('/expense', [TransactionController::class, 'expense']);
    // Total uang Halaman Transaksi
    Route::get('/total-money', [TransactionController::class, 'totalMoney']);
    
    
    // Semua data portofolio
    Route::get('/portofolio', [PortofolioController::class, 'index']);
    
    // detail portofolio
    Route::get('/portofolio/{id}', [PortofolioController::class, 'detail']);

    // insert data portofolio
    Route::post('/portofolio', [PortofolioController::class, 'store']);
    
    // get portofolio buat form input transaksi portofolio
    Route::get('/get-portofolio', [PortotransController::class, 'getPortofolio']);
    
    // Insert data transaksi portofolio
    Route::post('/portofolio-transaction', [PortotransController::class, 'store']);



    // Total Target Portofolio
    Route::get('/total-target', [PortofolioController::class, 'TotalTarget']);
    // Total terkumpul portofolio
    Route::get('/total-terkumpul', [PortofolioController::class, 'TotalTerkumpul']);

    // Logout
    Route::get('/logout', [AuthController::class, 'logout']);

});
