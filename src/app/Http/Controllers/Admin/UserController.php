<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * ユーザー一覧（検索・権限フィルタ・ページネーション）
     * ?role=all|admin|member（既定: all）
     * ?q=キーワード（名前・メール・ID）
     */
    public function index(Request $request)
    {
        $role = $request->query('role', 'all');
        $q    = trim((string) $request->query('q', ''));

        $users = User::query()
            ->withCount('photos')
            ->when($role === 'admin', fn($q) => $q->where('is_admin', 1))
            ->when($role === 'member', fn($q) => $q->where('is_admin', 0))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('id', (int) $q);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends(['role' => $role, 'q' => $q]);

        return view('admin.users.index', compact('users'));
    }

    /**
     * 管理者に昇格
     */
    public function promote(User $user)
    {
        // 既に管理者なら何もしない
        if ($user->is_admin) {
            return back()->with('status', "ID:{$user->id} は既に管理者です。");
        }

        $user->forceFill(['is_admin' => true])->save();

        return back()->with('status', "ID:{$user->id} を管理者に昇格しました。");
    }

    /**
     * 一般に降格
     * - 自分自身の降格は禁止
     * - 最後の管理者を消さない（管理者が1人しかいない場合は拒否）
     */
    public function demote(User $user)
    {
        $auth = auth()->user();

        if (!$user->is_admin) {
            return back()->with('status', "ID:{$user->id} は既に一般ユーザーです。");
        }

        // 自分自身の降格は禁止（ロックアウト防止）
        if ($auth && $auth->id === $user->id) {
            return back()->with('status', '自分自身を一般に降格することはできません。');
        }

        // 最後の管理者なら不可
        $adminCount = User::where('is_admin', true)->count();
        if ($adminCount <= 1) {
            return back()->with('status', '最後の管理者を降格することはできません。');
        }

        $user->forceFill(['is_admin' => false])->save();

        return back()->with('status', "ID:{$user->id} を一般に降格しました。");
    }

    /**
     * ユーザー削除
     * - 自分自身の削除は禁止
     * - 最後の管理者は削除不可
     * - 関連データの扱いは要件に応じて（今回はユーザーのみ削除）
     */
    public function destroy(User $user)
    {
        $auth = auth()->user();

        // 自分自身の削除は禁止
        if ($auth && $auth->id === $user->id) {
            return back()->with('status', '自分自身を削除することはできません。');
        }

        // 最後の管理者は削除不可
        if ($user->is_admin) {
            $adminCount = User::where('is_admin', true)->count();
            if ($adminCount <= 1) {
                return back()->with('status', '最後の管理者を削除することはできません。');
            }
        }

        DB::transaction(function () use ($user) {
            // 関連の投稿などをどう扱うかは運用設計次第
            // 例）投稿の user_id を null にする場合:
            // \App\Models\Photo::where('user_id', $user->id)->update(['user_id' => null]);
            $user->delete();
        });

        return back()->with('status', "ID:{$user->id} を削除しました。");
    }
}
