<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CareerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista de carreiras
        $careers = [
            'Militar',
            'Administrativa',
            'Educação',
            'Magistratura',
            'Bancária',
            'Fiscal',
            'Gestão Pública',
            'Controle',
            'Agências Reguladoras',
            'Jurídica',
        ];

        // Inserir as carreiras no banco de dados
        foreach ($careers as $career) {
            DB::table('careers')->insert([
                'name' => $career,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
