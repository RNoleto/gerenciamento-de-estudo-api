<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Career;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function index()
    {
        // return response()->json(Career::all());
        $arr = [
            [
                "carreira" => "teste json"
            ],
            [
                "carreira" => "teste json"
            ],
            [
                "carreira" => "teste json"
            ]
            ];
            return response()->json($arr); 

    }
}
