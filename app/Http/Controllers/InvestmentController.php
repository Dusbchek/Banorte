<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Investment;

class InvestmentController extends Controller
{
    public function update(Request $request, $id)
    {
        // Validar las entradas
        $request->validate([
            'user_id' => 'nullable|exists:users,id', // Si estás cambiando el `user_id`, debe existir en la tabla `users`
            'special_section_id' => 'nullable|exists:special_sections,id', // Asegurarte de que la sección especial exista
            'investment_type' => 'nullable|string',  // Si estás cambiando el tipo de inversión
            'amount' => 'nullable|numeric|min:1',    // Asegurarte de que la cantidad sea un número positivo
            'result' => 'nullable|numeric',          // Validar resultado de la inversión
            'status' => 'nullable|string',           // Validar el estado de la inversión
        ]);

        // Buscar la inversión por su id
        $investment = Investment::find($id);

        if (!$investment) {
            return response()->json(['message' => 'Inversión no encontrada'], 404);
        }

        // Actualizar los campos proporcionados
        if ($request->has('user_id')) {
            $investment->user_id = $request->user_id;
        }

        if ($request->has('special_section_id')) {
            $investment->special_section_id = $request->special_section_id;
        }

        if ($request->has('investment_type')) {
            $investment->investment_type = $request->investment_type;
        }

        if ($request->has('amount')) {
            $investment->amount = $request->amount;
        }

        if ($request->has('result')) {
            $investment->result = $request->result;
        }

        if ($request->has('status')) {
            $investment->status = $request->status;
        }

        // Guardar los cambios
        $investment->save();

        return response()->json(['message' => 'Inversión actualizada con éxito', 'data' => $investment]);
    }
}
