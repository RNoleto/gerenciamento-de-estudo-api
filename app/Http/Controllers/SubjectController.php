<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Normalizer;

class SubjectController extends Controller
{
    public function index()
    {
        return response()->json(Subject::all());
    }

    public function store(Request $request) {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:150'
            ]);
    
            $formattedName = ucwords(strtolower($validated['name']));
            $normalizedName = $this->removeAccents($formattedName); // Normalizar entrada do usuário
    
            // Buscar todas as matérias existentes e normalizar os nomes
            $existingSubjects = Subject::all()->map(function ($subject) {
                return $this->removeAccents($subject->name);
            });
    
            // Verificar se já existe uma matéria com nome similar
            if ($existingSubjects->contains($normalizedName)) {
                return response()->json([
                    'message' => 'Já existe uma matéria com esse nome!',
                    'success' => false
                ], 409);
            }
    
            // Criar a nova matéria se não existir
            $userSubject = Subject::create([
                'name' => $formattedName
            ]);
    
            return response()->json([
                'message' => 'Matéria criada com sucesso!',
                'data' => $userSubject
            ], 201);
    
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação!',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar a matéria.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Função para remover acentos corretamente
    private function removeAccents($string) {
        $string = Normalizer::normalize($string, Normalizer::FORM_D); // Normaliza os caracteres para decomposição
        return preg_replace('/[\pM]/u', '', $string); // Remove marcas de acento
    }
}
