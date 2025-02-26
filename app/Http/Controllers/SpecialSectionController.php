<?php

namespace App\Http\Controllers;


use App\Models\SpecialSection;
use Illuminate\Http\Request;

class SpecialSectionController extends Controller
{
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $specialSection = SpecialSection::findOrFail($id);

        $specialSection->user_id = $request->user_id;
        $specialSection->save();

        return response()->json([
            'message' => 'Sección especial actualizada con éxito.',
            'data' => $specialSection
        ], 200);
    }

    public function destroy($id)
    {
        $specialSection = SpecialSection::findOrFail($id);

        $specialSection->delete();

        return response()->json([
            'message' => 'Sección especial eliminada con éxito.'
        ], 200);
    }
}
