<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dialog;
use App\Models\Messenger;
use App\Models\Page;
use App\Models\RequestHistory;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'pages' => Page::query()->count(),
            'published_pages' => Page::query()->where('is_published', true)->count(),
            'messengers' => Messenger::query()->count(),
            'dialogs' => Dialog::query()->count(),
            'request_histories' => RequestHistory::query()->count(),
            'users' => User::query()->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
