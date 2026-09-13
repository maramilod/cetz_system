<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alert;
use App\Models\Student;

class AlertController extends Controller
{
    // عرض جميع التنبيهات
    public function index()
    {
        $alerts = Alert::with('student')->orderBy('created_at', 'desc')->get();
        return view('alerts.index', compact('alerts'));
    }



}
