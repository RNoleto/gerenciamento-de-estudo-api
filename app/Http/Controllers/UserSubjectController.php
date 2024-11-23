<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSubject;

class UserSubjectController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'user_id' => 'required|string',
        'subject_ids' => 'required|array',
        'subject_ids.*' => 'exists:subjects,id',
        'subjects_to_deactivate' => 'nullable|array',
        'subjects_to_deactivate.*' => 'exists:subjects,id',
    ]);

    $userId = $request->user_id;
    $subjectIds = $request->subject_ids;
    $subjectsToDeactivate = $request->input('subjects_to_deactivate', []);

    // Ativar ou atualizar matérias
    foreach ($subjectIds as $subjectId) {
        UserSubject::updateOrCreate(
            ['user_id' => $userId, 'subject_id' => $subjectId],
            ['ativo' => true]
        );
    }

    // Desativar matérias desmarcadas
    if (!empty($subjectsToDeactivate)) {
        UserSubject::where('user_id', $userId)
            ->whereIn('subject_id', $subjectsToDeactivate)
            ->update(['ativo' => false]);
    }

    return response()->json(['message' => 'Matérias salvas com sucesso.'], 200);
}

    public function index($userId)
{
    $subjects = UserSubject::where('user_id', $userId)
        ->where('ativo', true)
        ->with('subject')
        ->get();

    return response()->json($subjects);
}

    public function deactivate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $userSubject = UserSubject::where('user_id', $request->user_id)
            ->where('subject_id', $request->subject_id)
            ->first();

        if ($userSubject) {
            $userSubject->update(['ativo' => false]);
            return response()->json(['message' => 'Matéria desativada com sucesso.'], 200);
        }

        return response()->json(['error' => 'Matéria não foi encontrada para o usuário.'], 404);
    }
}
