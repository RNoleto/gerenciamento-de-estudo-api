<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Career;
use App\Models\Subject;
use App\Models\UserStudyRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats()
    {
        $totalUsers = User::count();
        $totalCareers = Career::count();
        $totalSubjects = Subject::count();

        $totalSecondsStudy = UserStudyRecord::where('ativo', 1)->sum('study_time');
        $hours = floor($totalSecondsStudy / 3600);
        $remainingSeconds = $totalSecondsStudy % 3600;
        $minutes = floor($remainingSeconds / 60);
        $formattedTotalHoursStudy = "{$hours}h {$minutes}min";

        $latestUserDate = User::latest('created_at')->value('created_at');

        $formattedDate = $latestUserDate ? $latestUserDate->format('d/m/Y'): null;

        return response()->json([
            'totalUsers' => $totalUsers,
            'latestRegistrationDate' => $formattedDate,
            'totalCareers' => $totalCareers,
            'totalSubjects' => $totalSubjects,
            'totalHoursStudy' => $formattedTotalHoursStudy,
        ]);
    }

    public function getStudySessionsChartData()
    {
        $records = UserStudyRecord::query()
            ->where('ativo', 1)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as sessions_count'),
                DB::raw('COALESCE(SUM(questions_resolved), 0) as questions_sum'),
                DB::raw('COALESCE(SUM(study_time), 0) as total_study_time_seconds')
            )
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        $period = now()->subDays(6)->startOfDay()->toPeriod(now()->endOfDay());
        $chartData = [];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $chartData[$formattedDate] = [
                'sessions_count' => $records[$formattedDate]->sessions_count ?? 0,
                'questions_sum' => $records[$formattedDate]->questions_sum ?? 0,
                'total_study_time_seconds' => $records[$formattedDate]->total_study_time_seconds ?? 0,
            ];
        }
        
        $labels = [];
        $sessionsData = [];
        $questionsData = [];
        $studyHoursData = [];

        foreach ($chartData as $date => $data) {
            $labels[] = Carbon::parse($date)->translatedFormat('d M');
            $sessionsData[] = $data['sessions_count'];
            $questionsData[] = $data['questions_sum'];
            
            $hours = round($data['total_study_time_seconds'] / 3600, 2);
            $studyHoursData[] = $hours;
        }

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
                ],
                [
                    'label' => 'Horas Estudadas',
                    'data' => $studyHoursData,
                    'borderColor' => '#F97316',
                    'backgroundColor' => 'rgba(249, 115, 22, 0.2)',
                    'tension' => 0.4,
                    'fill' => true,
                ]
            ]
        ]);
    }

    public function getCareerDistributionChartData()
    {
        $careerData = Career::query()
            ->join('user_career', 'careers.id', '=', 'user_career.career_id')
            ->select(
                'careers.name',
                DB::raw('COUNT(user_career.user_id) as user_count')
            ) 
            ->groupBy('careers.name')
            ->having(DB::raw('COUNT(user_career.user_id)'), '>', 0)
            ->orderBy(DB::raw('COUNT(user_career.user_id)'), 'desc')
            ->get();

        $labels = $careerData->pluck('name');
        $data = $careerData->pluck('user_count');

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Usuários',
                    'data' => $data,
                    'backgroundColor' => '#10B981',
                    'borderRadius' => 8,
                    'barThickness' => 15
                ]
            ]
        ]);
    }
}
