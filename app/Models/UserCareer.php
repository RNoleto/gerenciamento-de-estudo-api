<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Career;

class UserCareer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_career';

    protected $fillable = [
        'user_id',
        'career_id',
        'ativo',
    ];

    protected $dates = ['deleted_at'];

    public function career()
    {
        return $this->belongsTo(Career::class, 'career_id');
    }
}
