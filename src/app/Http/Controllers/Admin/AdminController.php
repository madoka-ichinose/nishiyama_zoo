<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Photo;
use App\Models\Contest;

class AdminController extends Controller
{
    public function dashboard()
    {
        $userCount          = User::count();
        $photoCount         = Photo::count();
        $pendingPhotoCount  = Photo::where('is_approved', false)->count();

        // 開催中（期間内）のコンテスト数をカウント
        $activeContestCount = Contest::active()->count();   // ← モデルのスコープ（下記）を使用

        return view('admin.dashboard', compact(
            'userCount',
            'photoCount',
            'pendingPhotoCount',
            'activeContestCount'
        ));
    }
}
