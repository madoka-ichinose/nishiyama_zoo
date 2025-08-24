<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ContestController extends Controller
{
    /**
     * コンテスト一覧＋新規作成フォーム
     */
    public function index()
    {
        // 開始日の降順で取得（最新が上）
        $contests = Contest::orderByDesc('start_at')->get();

        return view('admin.contests.index', compact('contests'));
    }

    /**
     * コンテスト作成
     * 期待する入力：title, start_at(datetime-local), end_at(datetime-local), description(任意)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'start_at'    => ['required', 'date'],
            'end_at'      => ['required', 'date', 'after:start_at'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        // datetime-local の値を Carbon へ
        // （アプリの timezone が Asia/Tokyo ならそのままでOK）
        $start = Carbon::parse($data['start_at']);
        $end   = Carbon::parse($data['end_at']);

        Contest::create([
            'title'       => $data['title'],
            'start_at'    => $start,
            'end_at'      => $end,
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('status', 'コンテストを作成しました。');
    }

    /**
     * （任意）今すぐ終了：end_at を現在に更新
     * ルート例: PUT /admin/contests/{contest}/end-now  -> name: admin.contests.endNow
     */
    public function endNow(Contest $contest)
    {
        if (now()->gt($contest->end_at)) {
            return back()->with('status', 'このコンテストは既に終了しています。');
        }
        $contest->end_at = now();
        $contest->save();

        return back()->with('status', 'コンテストを終了しました。');
    }

    public function edit(Contest $contest)
    {
        return view('admin.contests.edit', compact('contest'));
    }

    public function update(Request $request, Contest $contest)
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'start_at'    => ['required', 'date'],
            'end_at'      => ['required', 'date', 'after:start_at'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $contest->update([
            'title'       => $data['title'],
            'start_at'    => Carbon::parse($data['start_at']),
            'end_at'      => Carbon::parse($data['end_at']),
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('status', 'コンテスト情報を更新しました。');
    }

    /**
     * （任意）削除
     * ルート例: DELETE /admin/contests/{contest} -> name: admin.contests.destroy
     */
    public function destroy(Contest $contest)
    {
        $contest->delete();
        return back()->with('status', 'コンテストを削除しました。');
    }
}
