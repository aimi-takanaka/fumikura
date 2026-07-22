# アバター機能をやさしく理解する

文倉（fumikura）のプロフィール画面で、ユーザーがアバター画像をアップロード・変更・削除できる機能を追加しました。この記事では、**実装の順番**、**何をしたのか**、**よくある勘違い** をまとめます。

---

## 1. 結論：アバターは「DB にパスを保存し、ファイルは storage に置く」

アバター画像そのものは **データベースには入れません**。

| 保存場所 | 何を保存する？ |
| --- | --- |
| `users.avatar_path`（DB） | 画像ファイルの **場所**（例: `avatars/abc123.jpg`） |
| `storage/app/public/avatars/` | 画像ファイル **本体** |

画面に表示するときは、`avatar_path` から URL を作って `<flux:avatar>` に渡します。  
画像がなければ、`flux:avatar` が **イニシャル**（名前の頭文字）を表示します。

---

## 2. 実装の順番（何をしたか）

機能を作るときは、だいたい次の順番で進めました。

### ステップ 1：DB にカラムを追加（マイグレーション）

`database/migrations/2026_07_22_044533_add_avatar_to_users_table.php`

```php
$table->string('avatar_path')->nullable();
```

- `avatar_path` … 画像ファイルの保存場所を記録する欄
- `nullable()` … アバター未設定のユーザーは `null` のまま

実行コマンド:

```bash
php artisan migrate
```

### ステップ 2：User モデルを拡張

`app/Models/User.php`

- `avatar_path` を `$fillable` に追加
- `avatarUrl(): ?string` … 保存済み画像の公開 URL を返す（なければ `null`）
- `initials(): string` … 既存のイニシャル生成（画像がないときの表示用）

### ステップ 3：バリデーションルールを追加

`app/Concerns/ProfileValidationRules.php`

```php
'avatar' => ['nullable', 'image', 'max:2048'],
```

- 画像は **任意**（なくても OK）
- 画像ファイルのみ
- 最大 **2MB**

### ステップ 4：プロフィール画面でアップロード UI を作る

`resources/views/pages/settings/⚡profile.blade.php`

主な変更:

| 追加・変更 | 内容 |
| --- | --- |
| `WithFileUploads` | Livewire でファイルアップロードを使う |
| `public $avatar = null` | フォームで選んだファイルを入れる「箱」 |
| `wire:model="avatar"` | ファイル入力と `$avatar` をつなぐ |
| `updateProfileInformation()` | 保存時に画像を storage へ保存 |
| `removeAvatar()` | アバター削除ボタン用の処理 |
| `<flux:avatar>` | プレビュー・表示 |

### ステップ 5：共通コンポーネントを作る

`resources/views/components/user-avatar.blade.php`

物語詳細・コメント・ブックマーク一覧など、**複数ページで同じ見た目** にするため、`<x-user-avatar :user="$user" />` として再利用できるようにしました。

### ステップ 6：各ページにアバター表示を追加

| 場所 | ファイル |
| --- | --- |
| 物語詳細（著者・コメント） | `resources/views/pages/stories/⚡show.blade.php` |
| 画像詳細（物語一覧） | `resources/views/pages/images/⚡show.blade.php` |
| マイページ | `resources/views/pages/⚡mypage.blade.php` |
| お気に入り一覧 | `resources/views/pages/⚡bookmarks.blade.php` |
| ヘッダーメニュー | `resources/views/layouts/public.blade.php` |

### ステップ 7：テストを書く

`tests/Feature/ProfileAvatarTest.php`

- アップロードできる
- 削除できる
- 物語ページに著者・コメント者のアバターが表示される
- アバターがないユーザーはイニシャル表示

---

## 3. ユーザーの操作フロー

### 3-1. アバターを **変更** するとき（保存ボタン）

```
1. プロフィール画面を開く
2. 「ファイルを選択」で画像を選ぶ
   → $avatar 箱にファイルが入る
   → 画面上のアバターに一時プレビューが表示される
3. 「保存」ボタンを押す
   → updateProfileInformation() が動く
   → 古い画像ファイルがあれば削除
   → 新しい画像を storage に保存
   → avatar_path を DB に保存
```

### 3-2. アバターを **削除** するとき（Remove avatar ボタン）

```
1. 「Remove avatar」ボタンを押す
   → removeAvatar() が動く
2. storage の画像ファイルを削除
3. avatar_path を null にする
4. イニシャル表示に戻る
```

**保存ボタンは不要** です。削除ボタンだけで完結します。

### 3-3. 名前・メールだけ変更するとき

画像を選ばずに「保存」を押すと、`if ($this->avatar)` が false なので **アバターは触らず**、名前・メールだけ更新されます。

---

## 4. コードの読み方（重要ポイント）

### 4-1. `if ($this->avatar)` ＝「新しい画像を選んだか？」

```php
if ($this->avatar) {
    // 新しい画像を保存する処理
}
```

| 状態 | `$this->avatar` | `if` の結果 |
| --- | --- | --- |
| ファイルを選んでいない | `null` | false（中身を触らない） |
| ファイルを選んだ | ファイルオブジェクト | true（保存処理を実行） |

- `$avatar`（Blade）と `$this->avatar`（PHP）は **同じ箱**
- `wire:model="avatar"` でファイル入力とつながっている
- URL を比べているわけでは **ない**

### 4-2. `removeAvatar()` は「変更」ではなく「削除」

| 関数 | いつ動く？ | 何をする？ |
| --- | --- | --- |
| `updateProfileInformation()` | 「保存」ボタン | 新しい画像を **アップロードして変更** |
| `removeAvatar()` | 「Remove avatar」ボタン | アバターを **完全に削除** |

```php
public function removeAvatar(): void
{
    // avatar_path が null なら何もしない
    Storage::disk('public')->delete($user->avatar_path);  // ファイル削除
    $user->forceFill(['avatar_path' => null])->save();     // DB を空に
    $this->reset('avatar');                                 // フォームもクリア
}
```

### 4-3. アバター表示の `:src` の意味

```blade
:src="$avatar && $avatar->isPreviewable()
    ? $avatar->temporaryUrl()
    : auth()->user()->avatarUrl()"
```

**どの画像 URL を渡すか** を決めています。

```
【1】今、新しいファイルを選んだ？
      かつ プレビューできる種類？（JPEG / PNG など）
        ↓ はい
      temporaryUrl() … 保存前の一時プレビュー

【2】それ以外
        ↓
      avatarUrl() … 保存済みアバターの URL（なければ null）

【3】src が null
        ↓
      flux:avatar が initials（イニシャル）を表示
```

| 状態 | 画面に出るもの |
| --- | --- |
| アバター未設定 | イニシャル |
| 保存済みアバターあり | その画像 |
| 新しい画像を選んだ（保存前） | 選んだ画像のプレビュー |

「別のプレビュー画面」ではなく、**プロフィール画面のアバター丸い部分** にその場で表示されます。

### 4-4. `avatarUrl()` と `avatar_path` の違い

| 名前 | 種類 | 役割 |
| --- | --- | --- |
| `avatar_path` | DB のカラム | ファイルの **保存場所**（例: `avatars/xxx.jpg`） |
| `avatarUrl()` | User モデルのメソッド | ブラウザで表示できる **URL** を返す |

```php
// app/Models/User.php
public function avatarUrl(): ?string
{
    if ($this->avatar_path === null) {
        return null;
    }
    if (! Storage::disk('public')->exists($this->avatar_path)) {
        return null;
    }
    return Storage::disk('public')->url($this->avatar_path);
}
```

---

## 5. よくあった勘違い（学習メモ）

実装を理解する過程で、次のような勘違いがありました。同じところでつまずいた人の参考に。

### 勘違い 1：`if ($this->avatar)` は URL を比べている？

**違います。**

- 正しくは「フォームで **新しいファイルが選ばれたか**」を見ている
- 選ばれていなければ `null`（空の箱）
- 選ばれていればファイルが入っている → true

### 勘違い 2：`removeAvatar()` は画像を **変更** する処理？

**違います。**

- `removeAvatar()` … **削除**（ファイル消去 + DB を null）
- 画像の **変更** … `updateProfileInformation()` の `if ($this->avatar)` ブロック

### 勘違い 3：アバター設定は `CreateNewUser.php` でやる？

**違います。**

- `CreateNewUser.php` … **新規登録** のときだけ使う
- アバターアップロード … **プロフィール画面**（`⚡profile.blade.php`）で行う

登録時点ではアバターは未設定（`avatar_path = null`）で、後からプロフィールで設定します。

### 勘違い 4：イニシャル表示は PHP が切り替えている？

**直接は切り替えていません。**

- PHP は `avatarUrl()` で URL を渡す（なければ `null`）
- `flux:avatar` コンポーネントが「`src` があれば画像、なければ `initials`」と表示を決める

### 勘違い 5：`redirectIntended(default: route('topics.index'))` の `default` は「デフォルトルート」？

**Laravel のデフォルトルートという意味ではありません。**

これはメール認証再送の処理（`resendVerificationNotification()`）内のコードで:

```php
if ($user->hasVerifiedEmail()) {
    $this->redirectIntended(default: route('topics.index', absolute: false));
    return;
}
```

| 用語 | 意味 |
| --- | --- |
| `redirectIntended` | セッションに「行きたかった URL」があればそこへ移動 |
| `default:` | その URL が **なければ** ここへ行く（**予備の行き先**） |
| `topics.index` | このアプリのトピック一覧（`/topics`） |

メールが **すでに認証済み** なら、再送する必要がないので別ページへ移動する、という処理です。アバター機能そのものとは直接関係ありませんが、同じプロフィール画面のコードを読むときに混同しやすいのでメモしておきます。

---

## 6. ファイルと役割の一覧

| ファイル | 役割 |
| --- | --- |
| `database/migrations/2026_07_22_044533_add_avatar_to_users_table.php` | `avatar_path` カラム追加 |
| `app/Models/User.php` | `avatarUrl()` / `initials()` |
| `app/Concerns/ProfileValidationRules.php` | 画像バリデーション |
| `resources/views/pages/settings/⚡profile.blade.php` | アップロード・削除・プレビュー |
| `resources/views/components/user-avatar.blade.php` | 共通アバター表示 |
| `tests/Feature/ProfileAvatarTest.php` | 機能テスト |
| `lang/ja.json` | 「Avatar removed.」などの日本語 |

---

## 7. 表示されないときのチェック

画像をアップロードしたのにブラウザに表示されない場合:

```bash
php artisan storage:link
```

`public/storage` → `storage/app/public` のシンボリックリンクが必要です。  
1 回だけ実行すれば OK です。

---

## 8. 1 文でまとめ

> **アバターは `avatar_path` にファイルの場所を DB 保存し、画像本体は `storage/app/public/avatars/` に置く。プロフィール画面で選んだファイルは `$avatar` 箱に入り、保存で本番保存・Remove で削除・未設定なら `flux:avatar` がイニシャルを表示する。**

---

## 関連ファイル

- プロフィール画面: `resources/views/pages/settings/⚡profile.blade.php`
- User モデル: `app/Models/User.php`
- テスト: `tests/Feature/ProfileAvatarTest.php`
