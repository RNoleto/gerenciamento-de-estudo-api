<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCareer;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['userCareer.career'])
                     ->orderBy('created_at', 'desc')
                     ->paginate(15);

        return response()->json($users);
    }

    public function userCareer()
    {
        return $this->hasOne(UserCareer::class, 'user_id', 'firebase_uid');
    }
}
