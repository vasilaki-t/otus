<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatisticsCache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(StatisticsCache $statistics): View
    {
        $stats = $statistics->dashboardCounts();

        return view('admin.dashboard', compact('stats'));
    }
}
