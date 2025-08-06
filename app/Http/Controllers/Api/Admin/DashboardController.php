<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserStudyRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    public function getStudySessionsChartData()
    {
        // 1. Buscar os dados agrupados dos últimos 7 dias
        $records = UserStudyRecord::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as sessions_count'),
                DB::raw('SUM(questions_resolved) as questions_sum')
            )
            ->where('created_at', '>=', now()->subDays(6)->startOfDay()) // Últimos 7 dias incluindo hoje
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date'); // Transforma a collection em um array associativo pela data

        // 2. Criar um array com todos os dias do período (para não ter buracos)
        $period = now()->subDays(6)->startOfDay()->toPeriod(now()->endOfDay());
        $chartData = [];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $chartData[$formattedDate] = [
                'sessions_count' => $records[$formattedDate]->sessions_count ?? 0,
                'questions_sum' => $records[$formattedDate]->questions_sum ?? 0,
            ];
        }
        
        // 3. Formatar os dados para a estrutura que o Chart.js espera
        $labels = [];
        $sessionsData = [];
        $questionsData = [];

        foreach ($chartData as $date => $data) {
            // Formata o label para '05 Ago'
            $labels[] = Carbon::parse($date)->translatedFormat('d M');
            $sessionsData[] = $data['sessions_count'];
            $questionsData[] = $data['questions_sum'];
        }

        // 4. Retornar o JSON final
        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Sessões de Estudo',
                    'data' => $sessionsData,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Questões Respondidas',
                    'data' => $questionsData,
                    'borderColor' => '#20B2AA',
                    'backgroundColor' => 'rgba(32, 178, 170, 0.2)',
                    'tension' => 0.4,
                    'fill' => true,
                ]
            ]
        ]);
    }
}
