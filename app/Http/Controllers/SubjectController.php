<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubjectController extends Controller
{
    public function index()
    {
        return response()->json(Subject::all());
    }

    public function store(Request $request) {
        try{
            $validated = $request->validate([
                'name' => 'required|string|max:150'
            ]);

            $formattedName = ucwords(strtolower($validated['name']));

            $userSubject = Subject::create([
                'name' => $formattedName
            ]);
            
            return response()->json([
                'message' => 'Matéria criada com sucesso!',
                'data' => $userSubject
            ], 201);

        }catch(ValidationException $e){
            return response()->json([
                'message' => 'Erro de validação!',
                'errors' => $e->errors()
            ], 422);
        } catch(Exception $e){
            return response()->json([
                'message' => 'Erro ao criar a matéria.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
