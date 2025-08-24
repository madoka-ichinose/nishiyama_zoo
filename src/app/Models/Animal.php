<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'habitat', 'favorite_food', 'image_path',
    ];

    /** 動物に紐づく写真（公開分のみ取得するならスコープを併用） */
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }
}
