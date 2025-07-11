<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SyncFirebaseUid extends Command
{
    protected $signature = 'sync:firebase-uid';
    protected $description = 'Copia o clerk_id para firebase_uid em todos os usuários que ainda não possuem firebase_uid';

    public function handle()
    {
        $count = User::whereNull('firebase_uid')
            ->whereNotNull('clerk_id')
            ->update(['firebase_uid' => \DB::raw('clerk_id')]);

        $this->info("$count usuários atualizados com firebase_uid igual ao clerk_id.");
    }
} 