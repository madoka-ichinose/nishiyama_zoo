<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Contest;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    /**
     * 写真一覧（承認済みのみ）
     */
    public function index()
    {        
        $photos = Photo::with('user','animal')
            ->approved()
            ->visible()
            ->latest()
            ->paginate(12);

        return view('photos.index', compact('photos'));
    }

    /**
     * 新規投稿フォーム表示（ログイン必須ルートで保護）
     */
    public function create()
    {
        $animals = Animal::orderBy('name')->get();
        $activeContest = Contest::currentlyActive()->orderByDesc('start_at')->first();

        return view('photos.create', compact('animals', 'activeContest'));
    }

    /**
     * 新規投稿保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'animal_id'  => ['required', 'exists:animals,id'],
            'image'      => ['required', 'image', 'max:20480'], // KB単位
            'comment'    => ['nullable', 'string', 'max:2000'],
            'contest_id' => ['nullable', 'exists:contests,id'],
        ]);

        // contest_id が送られた場合は、開催中か再チェック（安全側）
        $contestId = null;
        if ($request->filled('contest_id')) {
            $contest = Contest::currentlyActive()->find($request->contest_id);
            if ($contest) {
                $contestId = $contest->id;
            }
        }

        // 画像保存（storage/app/public/photos） → asset('storage/...')で表示
        $path = $request->file('image')->store('photos', 'public');

        Photo::create([
            'user_id'     => $request->user()->id,
            'animal_id'   => $validated['animal_id'],
            'contest_id'  => $contestId,
            'image_path'  => $path,
            'comment'     => $validated['comment'] ?? null,
            'is_approved' => false, // 承認待ちで作成
        ]);

        return redirect()->route('mypage.index')->with('status', '投稿を受け付けました。承認後に公開されます。');
    }

    public function destroy(int $id)
    {
        $photo = Photo::findOrFail($id);
        $this->authorize('delete', $photo);

        $photo->delete(); // SoftDeletes 前提

        return back()->with('status', '投稿を削除しました。');
    }
}
