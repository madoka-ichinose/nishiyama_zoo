<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Photo;
use App\Models\User;
use App\Models\Animal;
use Illuminate\Support\Facades\Storage;

class PhotosTableSeeder extends Seeder
{
    public function run()
    {
        // 投稿者（最初のユーザーを仮利用）
        $user = User::first();
        if (!$user) {
            $this->command->warn('User が存在しないためスキップしました');
            return;
        }

        // 動物（適当に最初の動物）
        $animal = Animal::first();
        if (!$animal) {
            $this->command->warn('Animal が存在しないためスキップしました');
            return;
        }

        // サンプル画像を storage にコピー（public/photos ディレクトリ）
        Storage::disk('public')->makeDirectory('photos');
        $sampleImagePath = 'photos/sample1.jpg';

        if (!Storage::disk('public')->exists($sampleImagePath)) {
            // プロジェクトの public/images からコピー
            if (file_exists(public_path('images/red_panda2.png'))) {
                Storage::disk('public')->put(
                    $sampleImagePath,
                    file_get_contents(public_path('images/red_panda2.png'))
                );
            }
        }

        // ダミー投稿
        Photo::create([
            'user_id'     => $user->id,
            'animal_id'   => $animal->id,
            'image_path'  => $sampleImagePath,
            'comment'     => 'かわいいレッサーパンダです！',
            'is_approved' => true,   // 管理者承認済みとする
            'is_visible'  => true,
        ]);
    }
}
