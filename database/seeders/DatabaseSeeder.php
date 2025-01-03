<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //Chama o UserSeeder
        $this->call(UserSeeder::class);
        $this->call(CareerSeeder::class);
        $this->call(SubjectSeeder::class);
    }
}
