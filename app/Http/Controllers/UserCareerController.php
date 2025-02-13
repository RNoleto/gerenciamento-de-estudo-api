<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserCareer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserCareerController extends Controller {
    public function index() {
        $userCareers = UserCareer::with('career')->get();
        return response()->json($userCareers);
    }

    public function store(Request $request) {
        try{
            $validated = $request->validate([
                'user_id' => 'required|string|max:255',
                'career_id' => 'required|exists:careers,id',
            ]);

            $userCareer = UserCareer::updateOrCreate(
                ['user_id' => $validated['user_id']],
                ['career_id' => $validated['career_id']]
            );

            return response()->json(['message' => 'Carreira atribuída ao usuário com sucesso!', 'data' => $userCareer], 200);

        }catch(ValidationException $e){
            return response()->json([
                'message' => 'Erro de validação!',
                'errors' => $e->errors()
            ], 422);
        }catch(Exception $e){
            return response()->json([
                'message' => 'Erro ao atribuir a carreira ao usuário.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Verificar se o usuário tem alguma carreira salva
    public function getUserCareer($userId) {
        $userCareer = UserCareer::where('user_id', $userId)->first();

        if ($userCareer) {
            return response()->json($userCareer);
        }

        return response()->json(['id' => 0, 'career' => null]);
    }

    public function getCareerByUser($user_id)
    {
        // Recupera a carreira associada ao usuário, incluindo o nome da carreira
        $userCareer = UserCareer::where('user_id', $user_id)
            ->with('career')
            ->first();

            if($userCareer){
                return response()->json([
                    'career_name' => $userCareer->career ? $userCareer->career->name : null,
                ]);
            } else {
                return response()->json(['message' => 'Carreira não encontrada'], 404);
            }
    }

    public function destroy($id) {
        $userCareer = UserCareer::findOrFail($id);

        $userCareer->ativo = false;
        $userCareer->save();

        $userCareer->delete();

        return response()->json(['message' => 'Relação excluída com sucesso!'], 200);
    }
}
