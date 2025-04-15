<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SupportRequestMail;

class SupportController extends Controller
{
    public function sendSupport(Request $request)
    {
        // Validação dos dados recebidos
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Enviar o e-mail para o endereço desejado
        Mail::to('devnoleto@gmail.com')->send(new SupportRequestMail($validatedData));

        return response()->json(['message' => 'Sua mensagem foi enviada com sucesso!'], 200);
    }
}
