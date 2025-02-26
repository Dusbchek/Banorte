<?php

namespace App\Http\Controllers;

use App\Models\InvestmentResult;
use Illuminate\Http\Request;

class InvestmentResultController extends Controller
{
    public function update(Request $request, $id)
    {
        // Validar las entradas
        $request->validate([
            'investment_id' => 'nullable|exists:investments,id',  // Verificar que el `investment_id` sea válido
            'result' => 'nullable|numeric',                       // Validar el resultado de la inversión
            'date' => 'nullable|date',                             // Verificar que la fecha sea válida
        ]);

        // Buscar el resultado de la inversión por id
        $investmentResult = InvestmentResult::find($id);

        if (!$investmentResult) {
            return response()->json(['message' => 'Resultado de inversión no encontrado'], 404);
        }

        // Actualizar los campos proporcionados
        if ($request->has('investment_id')) {
            $investmentResult->investment_id = $request->investment_id;
        }

        if ($request->has('result')) {
            $investmentResult->result = $request->result;
        }

        if ($request->has('date')) {
            $investmentResult->date = $request->date;
        }

        // Guardar los cambios
        $investmentResult->save();

        return response()->json(['message' => 'Resultado de inversión actualizado con éxito', 'data' => $investmentResult]);
    }
}
