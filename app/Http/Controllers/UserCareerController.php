<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserCareer;
use Illuminate\Http\Request;

class UserCareerController extends Controller
{
    public function index()
    {
        $userCareers = UserCareer::with('career')->get();
        return response()->json($userCareers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string|max:255',
            'career_id' => 'required|exists:careers,id',
        ]);

        $userCareer = UserCareer::updateOrCreate(
            ['user_id' => $validated['user_id']],
            ['career_id' => $validated['career_id']]
        );

        return response()->json(['message' => 'Carreira atribuída ao usuário com sucesso!', 'data' => $userCareer], 200);
    }

    public function destroy($id)
    {
        $userCareer = UserCareer::findOrFail($id);
        
        $userCareer->ativo = false;
        $userCareer->save();
        
        $userCareer->delete();

        return response()->json(['message' => 'Relação excluída com sucesso!'], 200);
    }
}
