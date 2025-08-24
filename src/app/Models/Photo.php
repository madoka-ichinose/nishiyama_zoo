<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Photo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'animal_id', 'contest_id',
        'image_path', 'comment',
        'is_approved', 'approved_at', 'approved_by',
        'likes_count','is_visible','hidden_at','hidden_reason','path','caption',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_visible'  => 'boolean',
        'approved_at' => 'datetime',
        'deleted_at'  => 'datetime',
        'hidden_at'   => 'datetime',
        'is_pickup'    => 'boolean',
        'pickup_from'  => 'datetime',
        'pickup_until' => 'datetime',
    ];

    /** 投稿者 */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** 対象動物 */
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    /** 応募先コンテスト（任意） */
    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    /** 承認した管理者（users.id を参照） */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** 写真についたいいね */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /** 公開（承認済）のみ */
    public function scopeApproved($q)
    {
        return $q->where('is_approved', true);
    }

    /** 指定動物の写真に絞る */
    public function scopeForAnimal($q, $animalId)
    {
        return $q->where('animal_id', $animalId);
    }

    /** 新着順／人気順ヘルパー */
    public function scopeNewest($q)
    {
        return $q->orderByDesc('created_at');
    }
    public function scopeMostLiked($q)
    {
        return $q->orderByDesc('likes_count')->orderByDesc('created_at');
    }

    public function scopePublic($q) {
        return $q->where('is_approved', true)->where('is_visible', true);
    }

    public function scopePickup($q)
    {
        $now = now();
        return $q->public()
                 ->where('is_pickup', true)
                 ->where(function($qq) use ($now) {
                    $qq->whereNull('pickup_from')->orWhere('pickup_from', '<=', $now);
                 })
                 ->where(function($qq) use ($now) {
                    $qq->whereNull('pickup_until')->orWhere('pickup_until', '>=', $now);
                 });
    }

    public function scopeVisible($q){ return $q->where('is_visible', true); }

    // ===== ステータス互換層 =====
    public function getStatusAttribute(): string
    {
        // status カラムがあるならそれを優先
        if (array_key_exists('status', $this->attributes) && $this->attributes['status']) {
            return $this->attributes['status']; // 'pending'|'approved'|'rejected'
        }
        // 無い場合は is_approved から推定（null:保留, true:承認, false:却下）
        if (is_null($this->is_approved)) return 'pending';
        return $this->is_approved ? 'approved' : 'rejected';
    }

    public function scopeForContest($q, $contestId)
    {
        return $q->where('contest_id', $contestId);
    }

    public function scopeStatus($q, ?string $status)
    {
        if (!$status) return $q;
        // status カラムが無い環境向けに is_approved へフォールバック
        return $q->when(
            $this->hasStatusColumn(),
            fn($qq) => $qq->where('status', $status),
            function ($qq) use ($status) {
                return match ($status) {
                    'pending'  => $qq->whereNull('is_approved'),
                    'approved' => $qq->where('is_approved', true),
                    'rejected' => $qq->where('is_approved', false),
                    default    => $qq,
                };
            }
        );
    }

    public function scopeKeyword($q, ?string $kw)
    {
        if (!$kw) return $q;
        return $q->where(function ($qq) use ($kw) {
            $qq->where('caption', 'like', "%{$kw}%")
               ->orWhere('hidden_reason', 'like', "%{$kw}%");
        });
    }

    public function scopeUserNameLike($q, ?string $name)
    {
        if (!$name) return $q;
        return $q->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$name}%"));
    }

    public function scopeDateRange($q, ?string $from, ?string $to)
    {
        if ($from) $q->whereDate('created_at', '>=', $from);
        if ($to)   $q->whereDate('created_at', '<=', $to);
        return $q;
    }

    protected function hasStatusColumn(): bool
    {
        static $has = null;
        if ($has === null) {
            $table = $this->getTable();
            $has = \Illuminate\Support\Facades\Schema::hasColumn($table, 'status');
        }
        return $has;
    }
}
