<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContestPhotoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','can:isAdmin']);
    }

    /**
     * Laravel 9未満など Request::string() が無い環境向けヘルパ
     */
    private function s(Request $request, string $key, string $default = ''): string
    {
        $v = $request->input($key, $default);
        // string以外も確実に文字列へ
        return is_string($v) ? trim($v) : trim((string) $v);
    }

    public function index(Contest $contest, Request $request)
    {
        $status  = $this->s($request, 'status'); // pending|approved|rejected|''
        $visible = $request->input('visible');    // '1'|'0'|null
        $kw      = $this->s($request, 'q');
        $uname   = $this->s($request, 'user');
        $from    = $this->s($request, 'from');
        $to      = $this->s($request, 'to');
        $sort    = $this->s($request, 'sort', '-id');

        $query = Photo::query()
            ->with(['user'])
            ->forContest($contest->id)
            ->status($status)
            ->visible($visible)
            ->keyword($kw)
            ->userNameLike($uname)
            ->dateRange($from, $to);

        // 並び替え："-id" なら desc、"created_at" など任意
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column    = ltrim($sort, '-');
        if (!in_array($column, ['id','created_at','updated_at'])) $column = 'id';
        $query->orderBy($column, $direction);

        $photos = $query->paginate(20)->withQueryString();

        // ===== 集計 =====
        $countsByStatus = Photo::forContest($contest->id)
            ->selectRaw("CASE 
                WHEN (is_approved IS NULL) THEN 'pending' 
                WHEN (is_approved = 1) THEN 'approved' 
                ELSE 'rejected' END as s, COUNT(*) as c")
            ->groupBy('s')->pluck('c','s');

        $totals = [
            'total'      => (int) ($countsByStatus['pending'] ?? 0) + (int) ($countsByStatus['approved'] ?? 0) + (int) ($countsByStatus['rejected'] ?? 0),
            'pending'    => (int) ($countsByStatus['pending'] ?? 0),
            'approved'   => (int) ($countsByStatus['approved'] ?? 0),
            'rejected'   => (int) ($countsByStatus['rejected'] ?? 0),
            'visible'    => (int) Photo::forContest($contest->id)->where('is_visible', true)->count(),
            'hidden'     => (int) Photo::forContest($contest->id)->where('is_visible', false)->count(),
            'participants'=> (int) Photo::forContest($contest->id)->distinct('user_id')->count('user_id'),
        ];

        // 日別投稿数（直近30日）
        $daily = Photo::forContest($contest->id)
            ->selectRaw('DATE(created_at) d, COUNT(*) cnt')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('d')->orderBy('d')->get();

        return view('admin.contests.photos.index', compact('contest','photos','totals','daily','status','visible','kw','uname','from','to','sort'));
    }

    public function bulkUpdate(Contest $contest, Request $request)
    {
        $action = $this->s($request, 'action');
        $ids    = $request->input('ids', []);
        if (!in_array($action, ['approve','reject','hide','unhide','delete']) || empty($ids)) {
            return back()->with('error', '操作または対象が不正です。');
        }

        $q = Photo::forContest($contest->id)->whereIn('id', $ids);

        $affected = match ($action) {
            'approve' => $q->update(['is_approved' => true]),
            'reject'  => $q->update(['is_approved' => false]),
            'hide'    => $q->update(['is_visible' => false, 'hidden_at' => now(), 'hidden_reason' => $this->s($request, 'reason') ?: null]),
            'unhide'  => $q->update(['is_visible' => true,  'hidden_at' => null, 'hidden_reason' => null]),
            'delete'  => $q->delete(), // SoftDeletes
        };

        return back()->with('status', $affected.' 件を更新しました。');
    }

    public function exportCsv(Contest $contest, Request $request): StreamedResponse
    {
        $fileName = 'contest_'.$contest->id.'_photos_'.now()->format('Ymd_His').'.csv';

        $base = Photo::query()->with('user')->forContest($contest->id)
            ->status($this->s($request, 'status'))
            ->visible($request->input('visible'))
            ->keyword($this->s($request, 'q'))
            ->userNameLike($this->s($request, 'user'))
            ->dateRange($this->s($request, 'from'), $this->s($request, 'to'));

        $columns = ['id','user_name','caption','status','is_visible','created_at','hidden_reason'];

        return response()->streamDownload(function () use ($base, $columns) {
            $out = fopen('php://output', 'w');
            // ヘッダ
            fputcsv($out, $columns);
            $base->orderBy('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $p) {
                    fputcsv($out, [
                        $p->id,
                        optional($p->user)->name,
                        $p->caption,
                        $p->status,        // アクセサ
                        $p->is_visible ? 1 : 0,
                        $p->created_at?->format('Y-m-d H:i:s'),
                        $p->hidden_reason,
                    ]);
                }
            });
            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}