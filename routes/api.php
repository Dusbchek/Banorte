<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\SpecialSectionController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\InvestmentResultController;
use App\Models\FinancialAdvice as ModelsFinancialAdvice;
use App\Models\Investment;
use App\Models\InvestmentResult;
use App\Models\SpecialSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


Route::middleware('auth:sanctum')->put('/special_sections/{id}', [SpecialSectionController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/special_sections/{id}', [SpecialSectionController::class, 'destroy']);
Route::middleware('auth:sanctum')->put('/investments/{id}', [InvestmentController::class, 'update']);
Route::middleware('auth:sanctum')->put('/investments-results/{id}', [InvestmentResultController::class, 'update']);

Route::middleware('auth:sanctum')->post('/financial-advice', function (Request $request) {
    
    $request->validate([
        'advice' => 'required|string', 
        'advice_type' => 'required|string', 
    ]);

    $financialAdvice = ModelsFinancialAdvice::create([
        'user_id' => $request->user()->id,  
        'advice' => $request->advice,
        'advice_type' => $request->advice_type,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json([
        'message' => 'Consejo financiero creado con éxito.',
        'financial_advice' => $financialAdvice,
    ], 201);
});

Route::middleware('auth:sanctum')->post('/investment-results', function (Request $request) {
   
    $request->validate([
        'investment_id' => 'required|exists:investments,id', 
        'result' => 'required|numeric',
        'date' => 'required|date',
    ]);

    $investmentResult = InvestmentResult::create([
        'investment_id' => $request->investment_id,
        'result' => $request->result,
        'date' => $request->date,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json([
        'message' => 'Resultado de la inversión creado con éxito.',
        'investment_result' => $investmentResult,
    ], 201);
});



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


Route::middleware('auth:sanctum')->get('/daily_incomes', function (Request $request) {
    $user = $request->user(); 

    if (!$user) {
        return response()->json(['error' => 'Usuario no autenticado'], 401);
    }

    $dailyIncome = DB::table('daily_incomes')
                     ->where('user_id', $user->id)
                     ->orderBy('created_at', 'desc') 
                     ->first();

    if (!$dailyIncome) {
        return response()->json(['error' => 'No se encontraron ingresos diarios para este usuario'], 404);
    }

    return response()->json(['balance_after' => $dailyIncome->balance_after]);
});


Route::middleware('auth:sanctum')->get('/investments', function (Request $request) {
    // Obtiene todas las inversiones
    $investments = Investment::all();

    // Devuelve las inversiones como respuesta JSON
    return response()->json(['investments' => $investments]);
});


Route::post("/logout", function (Request $request) {
    // Revocar el token actual del usuario
    $request->user()->currentAccessToken()->delete();

    return response()->noContent();  // Retorna un código de éxito 204 sin contenido
})->middleware('auth:sanctum');

Route::group(["middleware" => ["auth:sanctum"]],function(){

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

  
    



        Route::get('/transactions', function () {
            $transactions = DB::table('transactions')->get();
            return response()->json($transactions);
        });
    
        Route::get('/special_sections', function () {
            $specialSections = DB::table('special_sections')->get();
            return response()->json($specialSections);
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

    $token = $user->createToken('BanorteApp')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user,  
    ]);
});

