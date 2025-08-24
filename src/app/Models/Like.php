<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'photo_id',
    ];

    /** いいねを付けたユーザー */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** いいね対象の写真 */
    public function photo()
    {
        return $this->belongsTo(Photo::class);
    }
}
