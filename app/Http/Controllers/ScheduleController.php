<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduleItem;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function getSchedule($userId)
    {
        try {
            $scheduleItems = ScheduleItem::where('user_id', $userId)
                ->with('subject:id,name')
                ->orderBy('day_of_week')
                ->orderBy('sort_order')
                ->get();

            return response()->json($scheduleItems);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar cronograma para o usuário ' . $userId . ': ' . $e->getMessage());

            return response()->json(['message' => 'Erro interno ao buscar o cronograma.'], 500);
        }
    }

    public function saveSchedule(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'weeklyPlan' => 'required|array',
            'weeklyPlan.*.day' => 'required|string',
            'weeklyPlan.*.subjects' => 'nullable|array',
            'weeklyPlan.*.subjects.*.name' => 'required|string',
            'weeklyPlan.*.subjects.*.subject_id' => 'required|integer|exists:subjects,id',
        ]);

        $userId = $validated['user_id'];
        $weeklyPlanData = $validated['weeklyPlan'];

        // Usamos uma transação para garantir a consistência dos dados
        DB::transaction(function () use ($userId, $weeklyPlanData) {
            // Deleta todo o cronograma antigo do usuário.
            ScheduleItem::where('user_id', $userId)->delete();

            $dayMap = [
                'Domingo' => 7, 'Segunda-feira' => 1, 'Terça-feira' => 2,
                'Quarta-feira' => 3, 'Quinta-feira' => 4, 'Sexta-feira' => 5, 'Sábado' => 6
            ];

            // Recria o cronograma a partir dos dados do frontend
            foreach ($weeklyPlanData as $dayData) {
                if (!empty($dayData['subjects'])) {
                    foreach ($dayData['subjects'] as $index => $subject) {
                        ScheduleItem::create([
                            'user_id' => $userId,
                            'subject_id' => $subject['subject_id'],
                            'day_of_week' => $dayMap[$dayData['day']],
                            'sort_order' => $index,
                        ]);
                    }
                }
            }
        });

        // [MUDANÇA] Após salvar, busca o cronograma recém-criado para retornar ao frontend
        $newSchedule = $this->getSchedule($userId)->original;

        return response()->json([
            'message' => 'Cronograma salvo com sucesso!',
            'schedule' => $newSchedule // Retorna os dados com os IDs corretos
        ], 200);
    }
}
