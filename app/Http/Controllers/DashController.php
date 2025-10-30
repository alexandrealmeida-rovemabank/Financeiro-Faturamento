<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Logistica_reversa;
class DashController extends Controller
{

 public function index()
{
    $user = auth()->user(); // pega o usuário logado
    return view('home', compact('user'));
}


}
