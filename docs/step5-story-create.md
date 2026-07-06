# ステップ5 振り返り：ストーリー投稿機能

このドキュメントは「文倉」開発ステップ5の振り返りです。
理解度チェック（5/5）を踏まえ、基礎説明は最小限にし、**「なぜそう作るか」「安全な保存のしかた」**、そして**コードを読んで言語化する練習**を中心にまとめます。

---

## 0. このステップで作ったものを一言で

> ログインユーザーが、お題（画像）に対して**ストーリーを投稿できる**ようにした。

これで「読む」だけでなく「書く」ことができ、アプリの中心となる体験が動き始めました。

---

## 1. 全体の流れ

```
お題詳細ページ →「ストーリーを書く」ボタン
        │
        ▼
[門番] auth ミドルウェア …… ログインしてる？（未ログインはログイン画面へ）
        │
        ▼
投稿フォーム（Livewire）…… タイトル / 本文 / ステータス
        │  save()
        ▼
1. バリデーション（必須・文字列・ステータスは complete か draft）
2. そのお題に紐づくストーリーを作成（image_id は自動、user_id は自分）
        │
        ▼
お題詳細ページへ戻る + 成功メッセージ
```

---

## 2. 作った／変更したファイル

| ファイル | 役割 |
| --- | --- |
| `resources/views/pages/stories/⚡create.blade.php` | 投稿フォーム本体 |
| `routes/web.php` | `/images/{image}/stories/create` を `auth` で保護して追加 |
| `resources/views/pages/images/⚡show.blade.php` | 「ストーリーを書く」ボタンをリンク化 |
| `tests/Feature/StoryCreateTest.php` | 投稿・検証・表示を保証（5件） |

> 補足：URLを `/images/{image}/stories/create` という「入れ子」にしたのは、**どのお題へのストーリーか**をURL自身が表せるからです（要件の `/stories/create` を、お題つきに具体化した形）。

---

## 3. なぜそう作ったのか（設計の意図）

### 3.1 リレーション経由で作る

```php
$this->image->stories()->create([...]);
```

「このお題（image）のストーリーを作る」と書くだけで、**`image_id` が自動でセット**されます。
自分で `'image_id' => $this->image->id` と書く必要がなく、付け忘れも防げます。

### 3.2 検証済みの値だけを保存する（安全）

```php
$validated = $this->validate([...]);

$this->image->stories()->create([
    ...$validated,
    'user_id' => auth()->id(),
]);
```

- `$this->validate([...])` は **検証を通った値だけ**を返す
- それを `...$validated`（スプレッド）で展開し、`user_id` を足す
- ユーザーが送ってきた全データ（`$request->all()` 相当）を丸ごと保存しないのが**セキュリティのベストプラクティス**（勝手なカラムを書き換えられない）

### 3.3 ステータスは Enum で厳密に

```php
'status' => ['required', Rule::enum(StoryStatus::class)],
```

`complete` / `draft` 以外の値（改ざんされた入力など）は弾かれます。
ステップ1で作った `StoryStatus` がここでも安全装置として効いています。

---

## 4. コード読解トレーニング（コードを見て言語化する）

各コードの下の「読み上げ方」を見る前に、まず自分の言葉で説明してみましょう。

### 問1

```php
public function mount(Image $image): void
{
    $this->image = $image;
}
```

**読み上げ方**：「URLで指定されたお題（Image）を受け取り、この投稿フォームが“どのお題に対するものか”を保持する」

### 問2

```php
$validated = $this->validate([
    'title' => 'required|string|max:255',
    'body' => 'required|string',
    'status' => ['required', Rule::enum(StoryStatus::class)],
]);
```

**読み上げ方**：「タイトルは必須の文字列で255文字まで、本文は必須の文字列、ステータスは必須で StoryStatus のどれか——を検証し、通った値だけを $validated に受け取る」

### 問3

```php
$this->image->stories()->create([
    ...$validated,
    'user_id' => auth()->id(),
]);
```

**読み上げ方**：「このお題のストーリーとして、検証済みの値（タイトル・本文・ステータス）を展開し、投稿者IDに“今の自分”を加えて、1件作成する」

- `...$validated` = 検証済みの値をその場に広げる
- `auth()->id()` = 今ログインしている人のID
- `image_id` は `stories()` 経由なので自動で入る

### 問4

```php
session()->flash('status', 'ストーリーを投稿しました。');
$this->redirectRoute('images.show', $this->image, navigate: true);
```

**読み上げ方**：「成功メッセージを“次の1回だけ”持たせて、このお題の詳細ページへ素早く移動する」

### 問5

```blade
<flux:radio.group wire:model="status" label="ステータス">
    <flux:radio value="complete" label="完成（誰でも読めます）" />
    <flux:radio value="draft" label="散文・アイデア（下書き / ログインユーザーのみ）" />
</flux:radio.group>
```

**読み上げ方**：「ステータスを2択（完成／下書き）で選ばせ、選んだ値を status プロパティに結びつける」

- `wire:model="status"` = 選択がそのまま PHP 側の `$status` に反映される

### 言語化のコツ（再掲）

1. 左から右へ、主語・述語に変換
2. カッコの中身は「何を・どこに」
3. 戻り値は「〜を受け取る」
4. 記号は役割で覚える（`...`=展開、`=>`=ペア、`::`=そのクラスの機能）

---

## 5. 動作確認の方法

```bash
composer run dev
```

1. ログイン（例: `test@example.com` / `password`）
2. お題詳細ページ →「ストーリーを書く」
3. タイトル・本文・ステータスを入力して「投稿する」
4. 詳細ページに戻り、一覧に反映されることを確認
   - 「完成」で投稿 → 未ログインでも見える
   - 「散文・アイデア」で投稿 → ログイン中だけ見える

テスト:

```bash
php artisan test --filter=StoryCreateTest
```

---

## 6. 理解度セルフチェック（答えられればOK）

1. `$this->image->stories()->create(...)` で image_id はどうなる？（→ 自動でセット）
2. `Rule::enum(StoryStatus::class)` は何を防ぐ？（→ complete/draft 以外の不正値）
3. `...$validated` は何をしている？（→ 検証済みの値だけを展開して渡す）
4. `middleware('auth')` が無いと何が起きる？（→ 未ログインでも投稿できてしまう）
5. 投稿後にユーザーに起きることは？（→ 詳細ページへ戻り、成功メッセージが1回表示）

---

## 7. 要件の充足状況と次のステップ

充足した要件（3.4 ストーリー投稿）:

- ログインユーザーのみ投稿可 ✅
- タイトル・本文の入力 ✅
- 投稿時にステータス選択（完成 / 散文・アイデア）✅

まだの部分:

- 「ステータスは投稿後も変更可能」＝**編集機能**は未対応
- ストーリー個別の詳細ページ（コメント欄など）は未作成

次のステップ候補:

- **ストーリー詳細ページ** … 1編をじっくり読む（コメント機能の土台）
- **ストーリー編集機能** … 本人のみ・ステータス変更もここで

---

_この記事は「文倉」開発の学習記録です。ステップが進むごとに追記していきます。_
