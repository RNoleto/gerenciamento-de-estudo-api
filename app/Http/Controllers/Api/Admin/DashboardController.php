<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getStats()
    {
        $totalUsers = User::count();

        $latestUserDate = User::latest('created_at')->value('created_at');

        $formattedDate = $latestUserDate ? $latestUserDate->format('d/m/Y'): null;

        return response()->json([
            'totalUsers' => $totalUsers,
            'latestRegistrationDate' => $formattedDate,
        ]);
    }
}
