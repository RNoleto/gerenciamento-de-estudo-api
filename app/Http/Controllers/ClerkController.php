<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClerkController extends Controller
{
    /**
     * Atualiza os metadados privados e públicos do usuário no Clerk.
     */
    public function updateUserMetadata(Request $request)
    {
        $clerkUserId = $request->input('clerk_user_id');
        $stripeCustomerId = $request->input('stripeCustomerId');
        $stripeSubscriptionId = $request->input('stripeSubscriptionId');

        if (!$clerkUserId) {
            return response()->json(['error' => 'clerk_user_id is required'], 400);
        }

        $clerkApiKey = env('CLERK_SECRET_KEY');
        $endpoint = "https://api.clerk.dev/v1/users/{$clerkUserId}";

        $updateResponse = Http::withHeaders([
            'Authorization' => "Bearer {$clerkApiKey}",
            'Content-Type'  => 'application/json',
        ])->patch($endpoint, [
            // Atualize os metadados públicos (visíveis ao usuário)
            'public_metadata' => [
                'subscriptionPlan' => 'premium'
            ],
            // Atualize os metadados privados (dados sensíveis)
            'private_metadata' => [
                'stripeCustomerId' => $stripeCustomerId,
                'stripeSubscriptionId' => $stripeSubscriptionId,
            ],
        ]);

        Log::info('Clerk update response:', [
            'status' => $updateResponse->status(),
            'body'   => $updateResponse->body()
        ]);

        if ($updateResponse->successful()) {
            return response()->json(['message' => 'User metadata updated successfully'], 200);
        } else {
            return response()->json(['error' => 'Failed to update user on Clerk'], 500);
        }
    }
}
