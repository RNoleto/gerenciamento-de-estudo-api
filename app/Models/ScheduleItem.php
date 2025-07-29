<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'day_of_week',
        'sort_order',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
