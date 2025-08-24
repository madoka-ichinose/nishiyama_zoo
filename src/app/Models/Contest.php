<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Contest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'theme',
        'start_at',
        'end_at',
        'is_active',
        'description',
    ];

    protected $casts = [
        'start_at'  => 'datetime',
        'end_at'    => 'datetime',
        'is_active' => 'boolean',
    ];

    /** 応募写真 */
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * 開催中（公開かつ期間内）
     *   - いま時点が start_at～end_at に含まれる
     *   - is_active = true（任意：カラムを使わない場合は where句を外してください）
     */
    public function scopeActive($query, ?Carbon $now = null)
    {
        $now = $now ?: now();

        return $query
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now);
    }

    /**
     * これから開催（公開かつ未来の開始）
     */
    public function scopeUpcoming($query, ?Carbon $now = null)
    {
        $now = $now ?: now();

        return $query
            ->where('start_at', '>', $now);
    }

    /**
     * 終了済み（公開かつ期間終了）
     */
    public function scopeEnded($query, ?Carbon $now = null)
    {
        $now = $now ?: now();

        return $query
            ->where('end_at', '<', $now);
    }

    /**
     * 互換：旧メソッド名を残しておきたい場合
     * （既存コードの置き換えなしで動かすため）
     */
    public function scopeCurrentlyActive($query, ?Carbon $now = null)
    {
        return $this->scopeActive($query, $now);
    }

    /**
     * ヘルパ：このインスタンスが「今」開催中か？
     */
    public function isActiveNow(?Carbon $now = null): bool
    {
        $now = $now ?: now();

        // is_active を尊重する場合
        if (!$this->is_active) {
            return false;
        }

        return ($this->start_at && $this->end_at)
            && $this->start_at->lte($now)
            && $this->end_at->gte($now);
    }
}
