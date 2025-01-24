<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory;

    protected $table = 'careers';

    // Adicione os campos que podem ser preenchidos em massa
    protected $fillable = [
        'name',
        'icon',
    ];

    public function UserCareers()
    {
        return $this->hasMany(UserCareer::class, 'career_id');
    }
}
