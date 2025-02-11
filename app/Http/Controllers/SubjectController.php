<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        return response()->json(Subject::all());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:150'
        ]);

        $userSubject = Subject::updateOrCreate(
            ['name' => $validated['name']]
        );

        return response()->json(['message' => 'Matéria criada com sucesso!', 'data' => $userSubject], 200);
    }
}