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
        $user = \App\Models\User::first();
        if (!$user) {
            $this->command->warn('User が存在しないためスキップしました');
            return;
        }

        // 対象動物と画像リスト
        $dummyPhotos = [
            'レッサーパンダ' => [
                ['file' => 'red_panda1.jpg', 'comment' => 'もぐもぐタイム！'],
                ['file' => 'red_panda2.jpg', 'comment' => 'かわいい。'],
                ['file' => 'red_panda3.png', 'comment' => 'お昼寝中のレッサーパンダ。'],
                ['file' => 'red_panda4.jpg', 'comment' => 'キュートなお顔。'],
                ['file' => 'red_panda5.jpg', 'comment' => 'しっぽがふわふわ！'],
            ],
            
            'リスザル' => [
                ['file' => 'squirrel_monkey.jpg', 'comment' => '元気に動き回るリスザル。'],
            ],
            'フランソワルトン' => [
                ['file' => 'francois_langur.jpg', 'comment' => '赤ちゃんと'],
            ],
            'シロテテナガザル' => [
                ['file' => 'gibbon.png', 'comment' => '鳴き声が特徴的なテナガザル。'],
            ],
        ];

        // 保存ディレクトリを確保
        Storage::disk('public')->makeDirectory('photos');

        foreach ($dummyPhotos as $animalName => $photos) {
            $animal = Animal::where('name', $animalName)->first();
            if (!$animal) {
                $this->command->warn("Animal '{$animalName}' が存在しないためスキップしました");
                continue;
            }

            foreach ($photos as $p) {
                $sampleImagePath = 'photos/' . $p['file'];

                // storage に存在しなければコピー
                if (!Storage::disk('public')->exists($sampleImagePath)) {
                    $source = public_path('images/' . $p['file']);
                    if (file_exists($source)) {
                        Storage::disk('public')->put(
                            $sampleImagePath,
                            file_get_contents($source)
                        );
                    } else {
                        $this->command->warn("画像ファイル {$source} が存在しません");
                        continue;
                    }
                }

                Photo::create([
                    'user_id'     => $user->id,
                    'animal_id'   => $animal->id,
                    'image_path'  => $sampleImagePath,
                    'comment'     => $p['comment'],
                    'is_approved' => true,
                    'is_visible'  => true,
                ]);
            }
        }
    }
}
