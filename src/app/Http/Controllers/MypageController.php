<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Photo;

class MypageController extends Controller
{
    /**
     * マイページ（自分の投稿一覧）
     * ?status=approved|pending|all で絞り込み可能
     */
    public function index(Request $request)
    {
        $user = $request->user(); // auth()->user() と同じ

        // 承認ステータスのクエリパラメータ（デフォルト: all）
        $status = $request->query('status', 'all'); // 'approved' | 'pending' | 'all'

        // ベースクエリ（自分の投稿のみ）
        $query = $user->photos()->with('animal')->latest();

        // 絞り込み
        if ($status === 'approved') {
            $query->where('is_approved', 1);
        } elseif ($status === 'pending') {
            $query->where('is_approved', 0);
        }

        // 一覧
        $photos = $query->paginate(12)->withQueryString();

        // サマリ件数（全体・承認・審査中）
        $totalCount     = $user->photos()->count();
        $approvedCount  = $user->photos()->where('is_approved', 1)->count();
        $pendingCount   = $user->photos()->where('is_approved', 0)->count();

        return view('mypage.index', [
            'photos'        => $photos,
            'approvedCount' => $approvedCount,
            'pendingCount'  => $pendingCount,
            'totalCount'    => $totalCount,
            'status'        => $status,
        ]);
    }
}
