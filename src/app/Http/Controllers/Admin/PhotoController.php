<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function __construct()
    {
        // ルートでも middleware を掛けていれば二重でもOK（安全側）
        $this->middleware(['auth', 'can:isAdmin']);
    }

    /**
     * 投稿一覧（承認待ち／承認済みをタブ切替）
     * GET /admin/photos?status=pending|approved|rejected
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $q      = trim((string) $request->query('q', ''));

        $photos = Photo::with(['user', 'animal'])
            ->when($status === 'pending', fn($q) => $q->where('is_approved', 0))
            ->when($status === 'approved', fn($q) => $q->where('is_approved', 1))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    // コメント・ID
                    $sub->where('comment', 'like', "%{$q}%")
                        ->orWhere('id', (int) $q);
                })
                // 動物名
                ->orWhereHas('animal', function ($a) use ($q) {
                    $a->where('name', 'like', "%{$q}%");
                })
                // 投稿者（名前・メール）
                ->orWhereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends([
                'status' => $status,
                'q'      => $q,
            ]);

        return view('admin.photos.index', compact('photos'));
    }

    /**
     * 承認処理
     */
    public function approve(Photo $photo)
    {
        if ($photo->is_approved) {
            return back()->with('status', "ID:{$photo->id} は既に承認済みです。");
        }

        $photo->is_approved = 1;
        // approved_at カラムがある場合のみ
        if ($photo->isFillable('approved_at') || \Schema::hasColumn($photo->getTable(), 'approved_at')) {
            $photo->approved_at = Carbon::now();
        }
        $photo->save();

        return back()->with('status', "ID:{$photo->id} を承認しました。");
    }

    /**
     * 削除処理
     * 画像ファイルも合わせて削除（public ディスク）
     */
    public function destroy(Photo $photo)
    {
        // 画像の実体を削除（存在チェック込）
        if (!empty($photo->image_path) && Storage::disk('public')->exists($photo->image_path)) {
            Storage::disk('public')->delete($photo->image_path);
        }

        $photo->delete();

        return back()->with('status', "ID:{$photo->id} を削除しました。");
    }

    public function hide(Photo $photo, Request $request)
{
    if (!$photo->is_visible) {
        return back()->with('status', "ID:{$photo->id} は既に非公開です。");
    }
    $photo->update([
        'is_visible'    => false,
        'hidden_at'     => Carbon::now(),
        'hidden_reason' => $request->input('reason'), // 任意
    ]);
    return back()->with('status', "ID:{$photo->id} を非公開にしました。");
}

public function unhide(Photo $photo)
{
    if ($photo->is_visible) {
        return back()->with('status', "ID:{$photo->id} は既に公開中です。");
    }
    $photo->update([
        'is_visible'    => true,
        'hidden_at'     => null,
        'hidden_reason' => null,
    ]);
    return back()->with('status', "ID:{$photo->id} を再公開しました。");
}

public function pickup(Photo $photo)
{
    $photo->is_pickup = true;
    if (is_null($photo->pickup_order)) {
        // ざっくり末尾に（最大値+1）
        $max = Photo::where('is_pickup', true)->max('pickup_order');
        $photo->pickup_order = (int)$max + 1;
    }
    $photo->save();

    return back()->with('status', 'この写真をPICK UPに設定しました。');
}

public function unpickup(Photo $photo)
{
    $photo->is_pickup = false;
    $photo->pickup_order = null;
    $photo->save();

    return back()->with('status', 'この写真のPICK UP設定を解除しました。');
}

// 任意：順序変更
public function setPickupOrder(Request $request, Photo $photo)
{
    $data = $request->validate([
        'pickup_order' => ['nullable','integer','min:1','max:9999'],
    ]);
    $photo->pickup_order = $data['pickup_order'];
    $photo->save();

    return back()->with('status', 'PICK UPの表示順を更新しました。');
}

}
