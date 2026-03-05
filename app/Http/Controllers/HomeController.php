<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $tasksCount = $user->tasks()->where('status', '!=', 'completed')->count();
        $filesCount = $user->files()->count();
        $recentTasks = $user->tasks()->latest()->take(5)->get();

        return view('dashboard', compact(
            'tasksCount',
            'filesCount',
            'recentTasks',
        ));
    }

    public function home()
    {
        return view('home');
    }
}
