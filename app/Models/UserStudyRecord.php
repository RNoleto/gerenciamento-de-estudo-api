<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStudyRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'topic',
        'study_time',
        'total_pauses',
        'questions_resolved',
        'correct_answers',
        'incorrect_answers',
        'ativo',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relação com a matéria
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
