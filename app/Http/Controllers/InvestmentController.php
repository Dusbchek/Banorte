<?php

// app/Http/Controllers/InvestmentController.php
namespace App\Http\Controllers;

use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        try {
            $investments = Investment::where('user_id', $user->id)
                ->get(['id', 'user_id', 'special_section_id', 'investment_type', 'amount', 'result', 'status', 'created_at', 'updated_at', 'name']);
            
            return response()->json([
                'data' => $investments
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en el servidor: ' . $e->getMessage()], 500);
        }
    }
}
