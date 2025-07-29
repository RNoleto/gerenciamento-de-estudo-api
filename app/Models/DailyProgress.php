<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        'schedule_item_id',
        'completion_date',
    ];

    public function scheduleItem()
    {
        return $this->belongsTo(ScheduleItem::class);
    }
}
