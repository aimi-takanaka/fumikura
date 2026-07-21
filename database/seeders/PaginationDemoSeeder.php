<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Story;
use App\Models\User;
use App\StoryStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaginationDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    private const int DEMO_COUNT = 10;

    /**
     * Seed dummy images and stories for pagination demo.
     */
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->first()
            ?? User::query()->where('is_admin', true)->first();

        if ($admin === null) {
            $this->command?->error('管理者ユーザーが見つかりません。先に php artisan db:seed を実行してください。');

            return;
        }

        $testUser = User::query()->where('email', 'test@example.com')->first();

        if ($testUser === null) {
            $this->command?->error('test@example.com のユーザーが見つかりません。先に php artisan db:seed を実行してください。');

            return;
        }

        $image = Image::query()->first();

        if ($image === null) {
            $image = Image::factory()->for($admin)->create([
                'title' => '【ダミー】お題 00',
                'file_path' => 'images/demo-placeholder.jpg',
            ]);
        }

        for ($i = 1; $i <= self::DEMO_COUNT; $i++) {
            $number = str_pad((string) $i, 2, '0', STR_PAD_LEFT);

            Image::factory()->for($admin)->create([
                'title' => "【ダミー】お題 {$number}",
                'file_path' => 'images/demo-placeholder.jpg',
            ]);

            Story::factory()
                ->for($image)
                ->for($testUser)
                ->create([
                    'title' => "【ダミー】記事 {$number}",
                    'body' => "ページネーション確認用のダミー記事です。（{$number}）",
                    'status' => $i % 3 === 0 ? StoryStatus::Complete : ($i % 3 === 1 ? StoryStatus::Idea : StoryStatus::Draft),
                ]);
        }

        $this->command?->info('ダミーお題 '.self::DEMO_COUNT.' 件とダミー記事 '.self::DEMO_COUNT.' 件を追加しました。');
        $this->command?->info('トップページとマイページ（test@example.com でログイン）でページネーションを確認できます。');
    }
}
