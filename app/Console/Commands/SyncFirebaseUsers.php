<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use App\Models\User;
use Illuminate\Support\Str;

class SyncFirebaseUsers extends Command
{
    protected $signature = 'sync:firebase-users';
    protected $description = 'Fetches all users from Firebase and syncs them with the local users table';

    // O construtor fica limpo, sem lógica
    public function __construct()
    {
        parent::__construct();
    }

    // Toda a lógica vai para o método handle()
    public function handle()
{
    $this->info('Starting user synchronization from Firebase...');

    try {
        $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
        $auth = $factory->createAuth();

        $firebaseUsers = $auth->listUsers($defaultMaxResults = 1000);
        
        $syncedCount = 0;

        foreach ($firebaseUsers as $firebaseUser) {
    /** @var \Kreait\Firebase\Auth\UserRecord $firebaseUser */

    // ---- INÍCIO DO NOVO BLOCO ----

    // 1. Encontra o usuário pelo clerk_id ou cria uma nova instância em memória
    $user = User::firstOrNew(['firebase_uid' => $firebaseUser->uid]);

    // 2. Preenche os atributos "normais" que estão no $fillable
    $user->fill([
        'name'      => $firebaseUser->displayName ?? 'Usuário',
        'email'     => $firebaseUser->email,
        // Evita re-gerar a senha se o usuário já existe e tem uma senha
        'password'  => $user->password ?? bcrypt(Str::random(20)),
        'email_verified_at' => $firebaseUser->emailVerified ? now() : null,
    ]);

    // 3. Desativa temporariamente os timestamps automáticos para este objeto específico
    $user->timestamps = false;

    // 4. Define as datas manualmente (agora elas serão respeitadas)
    // Se o usuário é NOVO (ainda não existe no banco), definimos o created_at original.
    if (!$user->exists) {
        $user->created_at = $firebaseUser->metadata->createdAt;
    }
    // O updated_at SEMPRE será o momento da sincronização.
    $user->updated_at = now();

    // 5. Salva o usuário no banco de dados
    $user->save();

    // ---- FIM DO NOVO BLOCO ----

    $syncedCount++;
}

        $this->info("Synchronization complete! {$syncedCount} users were synced.");

    } catch (\Exception $e) {
        $this->error('An error occurred during synchronization:');
        $this->error($e->getMessage());
        return 1;
    }

    return 0;
}
}