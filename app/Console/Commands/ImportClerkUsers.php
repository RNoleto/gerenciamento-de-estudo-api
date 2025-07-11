<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Client;
use App\Models\User;

class ImportClerkUsers extends Command
{
    protected $signature = 'import:clerk-users';
    protected $description = 'Importa usuários do Clerk para a tabela users';

    public function handle()
    {
        $clerkApiKey = env('CLERK_SECRET_KEY'); // ou CLERK_API_KEY se preferir
        $client = new Client();

        $this->info('Buscando usuários do Clerk...');

        $limit = 100; // máximo permitido pela API do Clerk
        $offset = 0;
        $totalImported = 0;

        do {
            $response = $client->request('GET', "https://api.clerk.dev/v1/users?limit=$limit&offset=$offset", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $clerkApiKey,
                ],
            ]);

            $users = json_decode($response->getBody(), true);

            $this->info('Usuários retornados nesta página: ' . count($users));

            foreach ($users as $user) {
                $clerkId = $user['id'];
                $email = $user['email_addresses'][0]['email_address'] ?? null;
                $firstName = $user['first_name'] ?? null;
                $lastName = $user['last_name'] ?? null;
                $userSince = isset($user['created_at']) ? date('Y-m-d H:i:s', $user['created_at']) : null;
                $name = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));

                if (!$email) {
                    $this->warn("Usuário $clerkId sem email, pulando...");
                    continue;
                }

                $existing = User::where('clerk_id', $clerkId)
                    ->orWhere('email', $email)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'clerk_id' => $clerkId,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'user_since' => $userSince,
                        'name' => $name,
                    ]);
                    $this->info("Usuário atualizado: $email");
                } else {
                    User::create([
                        'clerk_id' => $clerkId,
                        'email' => $email,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'user_since' => $userSince,
                        'name' => $name,
                        'password' => Hash::make(uniqid()),
                    ]);
                    $this->info("Usuário criado: $email");
                }
                $totalImported++;
            }

            $offset += $limit;
        } while (count($users) === $limit);

        $this->info("Importação concluída! Total de usuários importados/atualizados: $totalImported");
    }
}