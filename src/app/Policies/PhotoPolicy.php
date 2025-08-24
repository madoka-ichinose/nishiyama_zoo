<?php

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;

class PhotoPolicy
{
    /**
     * 管理者は全権限OK（細かく制御したい場合は削って個別に判定）
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_admin) {
            return true;
        }
        return null;
    }

    /** 一覧閲覧（公開分は誰でも可。管理側は before で許可） */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /** 1件閲覧（公開済は誰でも／未承認は本人のみ） */
    public function view(?User $user, Photo $photo): bool
    {
        if ($photo->is_approved) return true;
        return $user?->id === $photo->user_id;
    }

    /** 作成（ログイン必須） */
    public function create(User $user): bool
    {
        return true;
    }

    /** 更新（本人のみ） */
    public function update(User $user, Photo $photo): bool
    {
        return $user->id === $photo->user_id;
    }

    /** 削除（本人のみ） */
    public function delete(User $user, Photo $photo): bool
    {
        return $user->id === $photo->user_id;
    }

    /** 復元（必要なら） */
    public function restore(User $user, Photo $photo): bool
    {
        return $user->id === $photo->user_id;
    }

    /** 物理削除（通常は本人不可） */
    public function forceDelete(User $user, Photo $photo): bool
    {
        return false;
    }

    /** 承認（管理者のみ：beforeで許可される想定だが明示的にも用意） */
    public function approve(User $user, Photo $photo): bool
    {
        return $user->is_admin;
    }

    /** 非公開化/却下（管理者のみ） */
    public function unapprove(User $user, Photo $photo): bool
    {
        return $user->is_admin;
    }
}
