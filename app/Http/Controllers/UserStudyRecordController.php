<?php

namespace App\Http\Controllers;

use App\Models\UserStudyRecord;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserStudyRecordController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        return UserStudyRecord::with(['subject'])->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'topic' => 'nullable|string|max:255',
            'study_time' => 'required|integer|min:0',
            'total_pauses' => 'required|integer|min:0',
            'questions_resolved' => 'nullable|integer|min:0',
            'correct_answers' => 'required|integer|min:0',
            'incorrect_answers' => 'required|integer|min:0',
            'ativo' => 'nullable|integer|min:0|max:1',
        ]);

        // Definindo o valor padrão de 'ativo' como 1 se não for fornecido
        if (!isset($validated['ativo'])) {
            $validated['ativo'] = 1;
        }

        $record = UserStudyRecord::create($validated);

        return response()->json($record, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserStudyRecord $userStudyRecord) {
        return $userStudyRecord->load(['subject']);
    }
    /**
     * Display study data for a specified user_id.
     */

    public function getUserRecords($userId) {
        return UserStudyRecord::where('user_id', $userId)
        ->where('ativo', 1)
        ->with(['subject'])
        ->get();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserStudyRecord $userStudyRecord) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserStudyRecord $userStudyRecord) {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'subject_id' => 'required|integer',
            'topic' => 'nullable|string',
            'study_time' => 'required|integer',
            'total_pauses' => 'required|integer',
            'questions_resolved' => 'nullable|integer',
            'correct_answers' => 'required|integer',
            'incorrect_answers' => 'required|integer',
            'ativo' => 'nullable|integer|min:0|max:1',
        ]);

        // Definindo o valor padrão de 'ativo' como 1 se não for fornecido
        if (!isset($validated['ativo'])) {
            $validated['ativo'] = 1;
        }

        $userStudyRecord->update($validated);

        return response()->json($userStudyRecord, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserStudyRecord $userStudyRecord) {       
        
        $userStudyRecord->ativo = 0;
        $userStudyRecord->save();

        return response()->json(['message' => 'Registro de estudo excluído com sucesso!'], 200);
    }
}
