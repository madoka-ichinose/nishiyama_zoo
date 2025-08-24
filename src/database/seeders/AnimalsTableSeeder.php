<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Animal;

class AnimalsTableSeeder extends Seeder
{
    public function run(): void
    {
        // 既存と重複しないように name をキーに upsert/firstOrCreate
        $names = [
            'レッサーパンダ',
            'ボリビアリスザル',
            'シロテテナガザル',
            'フランソワルトン',
            'インドクジャク',
            'キンケイ',
            'ギンケイ',
            'コサンケイ',
            'タンチョウ',
        ];

        foreach ($names as $name) {
            Animal::firstOrCreate(['name' => $name]);
        }
    }
}
