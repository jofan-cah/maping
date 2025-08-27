<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */


    /**
     * Show the application dashboard.
     */
    public function index()
    {
        // Data dasar untuk dashboard
        $data = [
            'user' => Auth::user(),
            'page_title' => 'Dashboard',
            'welcome_message' => 'Selamat datang di dashboard!'
        ];

        // Nanti bisa ditambah data lain sesuai kebutuhan
        // Contoh:
        // $data['total_mitras'] = Mitra::count();
        // $data['total_mitra_turunans'] = MitraTurunan::count();
        // $data['recent_activities'] = Activity::latest()->take(5)->get();

        return view('dashboard', $data);
    }
}
