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
            'Direito Constitucional',
            'Direito Penal',
            'Direito Processual Penal',
            'Direito Civil',
            'Direito Processual Civil',
            'Direito do Trabalho',
            'Direito Processual do Trabalho',
            'Raciocínio Lógico',
            'Contabilidade Geral',
            'Contabilidade Pública',
            'Economia',
            'Administração Geral',
            'Administração Pública',
            'Gestão de Pessoas',
            'Gestão de Projetos',
            'Auditoria',
            'Finanças Públicas',
            'Orçamento Público',
            'Ética no Serviço Público',
            'Língua Estrangeira (Inglês/Espanhol)',
            'Geografia',
            'História',
            'Atualidades',
            'Noções de Sustentabilidade',
            'Noções de Direito Ambiental',
            'Noções de Direito Internacional',
            'Noções de Direito Empresarial',
            'Noções de Direito Previdenciário',
            'Noções de Direito Eleitoral',
            'Noções de Direito Financeiro',
            'Noções de Direito Econômico',
            'Noções de Direito Digital',
            'Direito Tributário',
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
