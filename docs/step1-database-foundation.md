# ステップ1 振り返り：データベースの土台づくり

このドキュメントは「文倉」開発のステップ1で何をしたのかを、初心者向けにまとめた振り返り記事です。
ステップ2に進む前にここを読み返せば、「自分はこれを作ったんだ」と自信を持って先へ進めます。

---

## 0. このステップで作ったものを一言で

> アプリの **土台（データベースと、それを操作するための道具）** を作った。

家に例えると「基礎工事」の段階です。目に見える画面（壁や屋根）はまだありませんが、この上にすべての機能が乗ります。

---

## 1. まず覚えておく4つの言葉

この4つが分かれば、ステップ1は理解できたと言ってOKです。

| 言葉 | ひとことで言うと | 家の例え |
| --- | --- | --- |
| **マイグレーション** | テーブル（表）の設計図をコードで作るもの | 部屋の間取り図 |
| **モデル** | 1つのテーブルをPHPから操作する「窓口」クラス | 各部屋の担当者 |
| **リレーション** | モデル同士の「つながり」 | 部屋と部屋をつなぐ廊下 |
| **ファクトリ / シーダー** | 動作確認用の偽データを自動生成する仕組み | 完成イメージ用の模型・仮の家具 |

---

## 2. どんなテーブル（データの入れ物）を作ったか

「文倉」に必要なデータの入れ物を6つ用意しました（`users` は元からあったので `is_admin` を追加）。

| テーブル | 何を入れる？ |
| --- | --- |
| `users` | ユーザー情報（＋ 管理者フラグ `is_admin` を追加） |
| `images` | 管理者が投稿したお題画像 |
| `stories` | ユーザーが書いたストーリー |
| `comments` | ストーリーへのコメント |
| `likes` | いいね（誰がどのストーリーを、を記録） |
| `bookmarks` | お気に入り（同上） |

### テーブル同士のつながり（全体像）

```
User（ユーザー）
 ├─ images     … 管理者として投稿したお題画像
 ├─ stories    … 書いたストーリー
 ├─ comments   … 書いたコメント
 ├─ likes      … 押したいいね
 └─ bookmarks  … 登録したお気に入り

Image（お題画像）
 ├─ user       … 投稿した管理者（1人）
 └─ stories    … その画像から生まれたストーリー（複数）

Story（ストーリー）
 ├─ image      … 元になったお題画像（1枚）
 ├─ user       … 書いた人（1人）
 ├─ comments   … ついたコメント（複数）
 ├─ likes      … ついたいいね（複数）
 └─ bookmarks  … 登録された数（複数）
```

ポイントは向きの違いです。

- **`belongsTo`（〜に属する）** … 「1つのものに属する」側。例：ストーリーは1枚の画像に属する。
- **`hasMany`（〜をたくさん持つ）** … 「複数を持つ」側。例：画像は複数のストーリーを持つ。

---

## 3. 実際に作った / 変更したファイル一覧

どこに何があるか分かると、後で見返しやすくなります。

### マイグレーション（設計図）: `database/migrations/`

- `..._create_images_table.php`
- `..._create_stories_table.php`
- `..._create_comments_table.php`
- `..._create_likes_table.php`
- `..._create_bookmarks_table.php`
- `..._add_is_admin_to_users_table.php`（users に管理者フラグを追加）

### モデル（テーブルの操作窓口）: `app/Models/`

- `Image.php` / `Story.php` / `Comment.php` / `Like.php` / `Bookmark.php`
- `User.php`（リレーションと `is_admin` の設定を追加）

### ステータス用の Enum: `app/StoryStatus.php`

ストーリーの状態を「決まった選択肢だけ」に限定する仕組み。

- `StoryStatus::Complete`（`'complete'` = 完成）
- `StoryStatus::Draft`（`'draft'` = 散文・アイデア）
- `->label()` を呼ぶと「完成」「散文・アイデア」という日本語ラベルが返る

### 偽データ生成: `database/factories/` と `database/seeders/DatabaseSeeder.php`

動作確認用に、ユーザー・画像・ストーリー・コメント等を自動生成。

### テスト: `tests/Feature/ModelRelationshipsTest.php`

作った土台が正しく動くかを自動でチェック（全6件パス）。

---

## 4. コードの読み方（例：Story モデル）

`app/Models/Story.php` を例に、各パーツが何なのかを説明します。

```php
namespace App\Models;              // このファイルの住所

use App\StoryStatus;               // 使う道具を短い名前で呼ぶための「持ち込み宣言」
use Illuminate\Database\Eloquent\Model;
// ...

class Story extends Model          // Model の機能を受け継いだ Story クラス
{
    // status を「ただの文字列」ではなく StoryStatus という選択肢型として扱う設定
    protected function casts(): array
    {
        return ['status' => StoryStatus::class];
    }

    // このストーリーが属する画像（1枚）
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    // このストーリーが持つコメント（複数）
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

覚えておくと良いこと:

- `namespace` / `use` は「準備」の行。アプリの動きそのものではない。
- `public function image()` のような**リレーションのメソッド**を書いておくと、
  後で `$story->image` と書くだけで、そのストーリーの画像を取り出せる。

---

## 5. 今のデータベースの状態を確認する方法

シーダーで偽データを入れてあるので、次のログインアカウントが使えます（パスワードはどちらも `password`）。

| 役割 | メールアドレス |
| --- | --- |
| 管理者 | `admin@example.com` |
| 一般ユーザー | `test@example.com` |

データを作り直したくなったら、次のコマンドで「全部消してから作り直し＋偽データ投入」ができます。

```bash
php artisan migrate:fresh --seed
```

テストを実行して土台が壊れていないか確認するには:

```bash
php artisan test --filter=ModelRelationshipsTest
```

---

## 6. 理解度セルフチェック

次の質問に自分の言葉でざっくり答えられれば、ステップ2に進んでOKです。

1. マイグレーションとは何をするもの？（→ テーブルの設計図をコードで作る）
2. モデルとは何？（→ 1つのテーブルを操作するPHPの窓口クラス）
3. `belongsTo` と `hasMany` の違いは？（→ 1つに属する / 複数を持つ）
4. `is_admin` は何のため？（→ 管理者かどうかの判定）
5. ファクトリ/シーダーは何のため？（→ 動作確認用の偽データ生成）

---

## 7. 次のステップ（ステップ2）

土台ができたので、次はいよいよ「画面で見えるもの」を作ります。候補は次の2つ。

- **管理者の画像アップロード機能** … データの入口を先に作る
- **トップページ（画像一覧の表示）** … まず画面に出して達成感を得る

どちらから進めても、この土台（モデル・リレーション）をそのまま使っていきます。

---

_この記事は「文倉」開発の学習記録です。ステップが進むごとに追記・更新していきます。_
