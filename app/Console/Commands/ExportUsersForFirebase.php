<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ExportUsersForFirebase extends Command
{
    protected $signature = 'export:firebase-users';
    protected $description = 'Exporta usuários para importação no Firebase';

    public function handle()
    {
        $users = User::all(['id','email', 'name', 'first_name', 'last_name', 'clerk_id']);
        file_put_contents('users_for_firebase.json', $users->toJson(JSON_PRETTY_PRINT));
        $this->info('Usuários exportados para users_for_firebase.json');
    }
}