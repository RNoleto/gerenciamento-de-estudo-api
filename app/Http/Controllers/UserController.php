<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserCareer;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['userCareer.career'])
                     ->orderBy('created_at', 'desc')
                     ->paginate(15);

        return response()->json($users);
    }

    public function show(User $user)
    {
        return response()->json($user->load('userCareer'));
    }

    public function userCareer()
    {
        return $this->hasOne(UserCareer::class, 'user_id', 'firebase_uid');
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'career_id' => 'nullable|exists:careers,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Atualiza apenas os campos que pertencem à tabela de usuários.
        $user->update($request->only(['name', 'email']));

        // A lógica para atualizar a carreira continua a mesma.
        if ($request->has('career_id')) {
            \App\Models\UserCareer::updateOrCreate(
                ['user_id' => $user->firebase_uid],
                ['career_id' => $request->input('career_id')]
            );
        }

        return response()->json(['message' => 'Usuário atualizado com sucesso.']);
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            return response()->json(['message' => 'Usuário deletado com sucesso.'], 200);
        } catch (\Exception $e){
            return response()->json(['error' => 'Ocorreu um erro ao deletar o usuário.'], 500);
        }
    }
}
