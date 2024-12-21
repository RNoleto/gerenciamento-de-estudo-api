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
            ['name' => 'Militar', 'icon' => 'material-symbols:military-tech'],
            ['name' => 'Administrativa', 'icon' => 'wpf:administrator'],
            ['name' => 'Educação', 'icon' => 'mdi:school'],
            ['name' => 'Magistratura', 'icon' => 'wpf:administrator'],
            ['name' => 'Bancária', 'icon' => 'mdi:bank'],
            ['name' => 'Fiscal', 'icon' => 'mdi:bank'],
            ['name' => 'Gestão Pública', 'icon' => 'wpf:administrator'],
            ['name' => 'Controle', 'icon' => 'wpf:administrator'],
            ['name' => 'Agências Reguladoras', 'icon' => 'wpf:administrator'],
            ['name' => 'Jurídica', 'icon' => 'wpf:administrator'],
            ['name' => 'Tecnologia', 'icon' => 'wpf:administrator'],
            ['name' => 'Saúde', 'icon' => 'mdi:medical-bag'],
            ['name' => 'Segurança Pública', 'icon' => 'mdi:shield-account'],
            ['name' => 'Engenharia', 'icon' => 'mdi:engine'],
            ['name' => 'Comunicação', 'icon' => 'mdi:comment-account'],
            ['name' => 'Cultura', 'icon' => 'mdi:palette'],
            ['name' => 'Meio Ambiente', 'icon' => 'mdi:tree'],
            ['name' => 'Transportes', 'icon' => 'mdi:bus'],
            ['name' => 'Agricultura', 'icon' => 'mdi:tractor'],
            ['name' => 'Ciências Sociais', 'icon' => 'mdi:account-group'],
            ['name' => 'Economia', 'icon' => 'mdi:currency-usd'],
            ['name' => 'Recursos Humanos', 'icon' => 'mdi:account-multiple'],
            ['name' => 'Arquitetura', 'icon' => 'mdi:city'],
            ['name' => 'Turismo', 'icon' => 'mdi:airplane'],
        ];

        // Inserir as carreiras no banco de dados
        foreach ($careers as $career) {
            DB::table('careers')->insert([
                'name' => $career['name'],
                'icon' => $career['icon'], 
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
