<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Career;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function index()
    {
        return response()->json(Career::all());

    }

    public function store(Request $request)
    {
        // Validação dos dados recebidos
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255', // O campo 'icon' é obrigatório
        ]);

        // Criar uma nova carreira
        $career = Career::create($validatedData);

        // Retornar a resposta
        return response()->json([
            'message' => 'Carreira cadastrada com sucesso!',
            'data' => $career
        ], 201);
    }
}
