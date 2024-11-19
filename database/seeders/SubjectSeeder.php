<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        // Lista de matérias
        $subjects = [
            'Matemática',
            'Português',
            'Informática',
            'Direito Administrativo',
            'Direito Tributário',
            'AFO',
        ];

        // Inserir as matérias no banco de dados
        foreach ($subjects as $subject) {
            DB::table('subjects')->insert([
                'name' => $subject,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
