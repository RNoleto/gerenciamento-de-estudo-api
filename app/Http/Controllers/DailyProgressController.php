<?php

namespace App\Http\Controllers;

use App\Models\DailyProgress;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyProgressController extends Controller
{
    public function getProgressForDate(Request $request, $userId)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $progress = DailyProgress::where('user_id', $userId)
            ->where('completion_date', $validated['date'])
            ->get(['schedule_item_id']);
            
        return response()->json($progress);
    }

    public function toggleProgress(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'schedule_item_id' => 'required|integer|exists:schedule_items,id',
        ]);

        $today = Carbon::today()->toDateString();

        $existingProgress = DailyProgress::where('user_id', $validated['user_id'])
            ->where('schedule_item_id', $validated['schedule_item_id'])
            ->where('completion_date', $today)
            ->first();
        
        if ($existingProgress) {
            $existingProgress->delete();
            return response()->json(['message' => 'Progresso desmarcado.', 'status' => 'removed']);
        } else {
            DailyProgress::create([
                'user_id' => $validated['user_id'],
                'schedule_item_id' => $validated['schedule_item_id'],
                'completion_date' => $today,
            ]);
            return response()->json(['message' => 'Progresso marcado.', 'status' => 'added'], 201);
        }
    }

    public function syncProgress(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
            'completed_ids' => 'present|array', // 'present' permite array vazio
            'completed_ids.*' => 'integer|exists:schedule_items,id',
        ]);

        $userId = $validated['user_id'];
        $date = $validated['date'];
        $completedIds = $validated['completed_ids'];

        DB::transaction(function () use ($userId, $date, $completedIds) {
            DailyProgress::where('user_id', $userId)->where('completion_date', $date)->delete();

            if (!empty($completedIds)) {
                $progressToInsert = [];
                foreach ($completedIds as $itemId) {
                    $progressToInsert[] = [
                        'user_id' => $userId,
                        'schedule_item_id' => $itemId,
                        'completion_date' => $date,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DailyProgress::insert($progressToInsert);
            }
        });

        return response()->json(['message' => 'Progresso sincronizado com sucesso.']);
    }
}