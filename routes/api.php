<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\SpecialSectionController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\InvestmentResultController;



Route::middleware('auth:sanctum')->put('/special_sections/{id}', [SpecialSectionController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/special_sections/{id}', [SpecialSectionController::class, 'destroy']);
Route::middleware('auth:sanctum')->put('/investments/{id}', [InvestmentController::class, 'update']);
Route::middleware('auth:sanctum')->put('/investments-results/{id}', [InvestmentResultController::class, 'update']);

Route::middleware('auth:sanctum')->post('/special-sections', function (Request $request) {
    $request->validate([
        'user_id' => 'required|exists:users,id', 
        'name' => 'required|string|max:255',
        'description' => 'nullable|string', 
        'balance' => 'required|numeric|min:0',
    ]);

    $specialSection = SpecialSection::create([
        'user_id' => $request->user_id,
        'name' => $request->name,
        'description' => $request->description ?? null,  
        'balance' => $request->balance,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json([
        'message' => 'Sección especial creada con éxito.',
        'special_section' => $specialSection,
    ], 201);  
});

Route::group(["middleware" => ["auth:santum"]],function(){

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

   


    Route::post("/logout", function (Request $request){

        $request->$user()->currentAccessToken()->delete();

        return response()->noContent();

        Route::get('/transactions', function () {
            $transactions = DB::table('transactions')->get();
            return response()->json($transactions);
        });
    
        Route::get('/special_sections', function () {
            $specialSections = DB::table('special_sections')->get();
            return response()->json($specialSections);
        });
    
        Route::get('/daily_incomes', function () {
            $dailyIncomes = DB::table('daily_incomes')->get();
            return response()->json($dailyIncomes);
        });
    
        Route::get('/daily_expenses', function () {
            $dailyExpenses = DB::table('daily_expenses')->get();
            return response()->json($dailyExpenses);
        });
    
        Route::get('/investments', function () {
            $investments = DB::table('investments')->get();
            return response()->json($investments);
        });
    
        Route::get('/investments_results', function () {
            $investmentsResults = DB::table('investments_results')->get();
            return response()->json($investmentsResults);
        });
});


} );

Route::post('/login', function (Request $request) {
   
    $request->validate([
        'name' => 'required|string|max:255',
    ]);


    $user = User::where('name', $request->name)->first();

 
    if (!$user) {
        return response()->json([
            'error' => 'El usuario no existe. Intenta nuevamente.'
        ], 404); 
    }

    $token = $user->createToken('YourAppName')->plainTextToken;

    // Devolver la respuesta con el token
    return response()->json([
        'token' => $token,
        'user' => $user,  
    ]);
});
