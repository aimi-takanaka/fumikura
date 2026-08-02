# お問い合わせフォームをやさしく理解する

文倉（fumikura）のお問い合わせフォームについて、**全体の流れ**と、学習中に出た質問をまとめます。  
専門用語はできるだけ少なくしています。

---

## 1. 結論：3つの役割がつながっている

お問い合わせフォームは、だいたい次の3つでできています。

| 役割 | たとえ | このプロジェクトでの場所 |
| --- | --- | --- |
| 書く場所 | 用紙に記入する | `resources/views/pages/⚡contact.blade.php` |
| 覚えておく場所 | 受付票を保管する | `ContactInquiry`（データベース） |
| メールにして送る場所 | 手紙のひな形＋配達 | `ContactInquiryMail` とメール設定 |

---

## 2. 全体の簡単な流れ

```text
1. 人が画面に書く
2. 「確認画面へ」で見直す
3. 「送信する」を押す
4. 内容をデータベースに保存する
5. メールの形にして届ける
```

コードだと、だいたいこうつながっています。

```text
画面で入力
   ↓ preview()
確認画面
   ↓ send()
ContactInquiry::create()   ← 覚えておく
   ↓
ContactInquiryMail を作成  ← メールの形にする
   ↓
Mail::to(...)->send(...)   ← 送る
```

### 入り口

`/contact` を開くと、お問い合わせ画面が出ます。

```php
// routes/web.php
Route::livewire('/contact', 'pages::contact')->name('contact');
```

トップページのフッター「お問い合わせ」も、この住所につながっています。

---

## 3. 各ステップをコードで見る

### ステップ A：書く場所

入力内容は、次の箱に入ります。

```php
public string $name = '';
public string $email = '';
public string $subject = '';
public string $body = '';
public bool $previewing = false;
```

フォームは「確認画面へ」を押すと `preview` が動きます。

```html
<form wire:submit="preview" class="flex flex-col gap-6">
```

### ステップ B：確認する

必須チェックをして、確認モードに切り替えます。

```php
public function preview(): void
{
    $this->validate($this->rules());
    $this->previewing = true;
}
```

ここでは **まだ保存もしない・メールも送らない** です。

### ステップ C：送信する → 覚えておく

```php
$inquiry = ContactInquiry::query()->create($validated);
```

チェック済みの入力を、お問い合わせ用テーブルに1件保存します。

### ステップ D：メールのひな形にして送る

```php
Mail::to(config('mail.contact_to'))->send(new ContactInquiryMail($inquiry));
```

- `config('mail.contact_to')` … 送り先（いまは `hello@example.com`）
- `ContactInquiryMail` … メールのひな形（件名・本文の形）

本文の見た目は `resources/views/mail/contact-inquiry.blade.php` です。

---

## 4. よくある質問（このチャットで聞いたこと）

### Q1. Mailable で作るのですか？

**はい。メールで受け取るなら Mailable を使うのが定石です。**

ただし Mailable だけで全部ではありません。

- 画面（フォーム）
- データベース保存
- Mailable（メールのひな形）
- メール送信設定（`.env` の `MAIL_*`）

がセットです。

### Q2. Mailable は「メールができるようになる設定」ですか？

**少し違います。**

| もの | 役割 | たとえ |
| --- | --- | --- |
| メール設定（`MAIL_*`） | 実際に送る道 | 郵便局 |
| Mailable | 送る手紙の内容 | 便箋に何を書くか |

ローカルでは `MAIL_MAILER=log` なので、本物のメールボックスではなくログに記録されます。

### Q3. Mailable は送信内容を入力するためのフォーマットですか？

**ほぼその理解で大丈夫です。**  
ただし、ユーザーが画面で入力するフォームとは別です。

- フォーム … 人が入力する画面
- Mailable … その内容を「メールの形」に整えるひな形

### Q4. F5（更新）のあとも入力を残したい。`old()` を使えばできますか？

**この画面では `old()` だけでは足りません。**

`old()` は、ふつうのフォーム送信後に戻ってきたとき用です。  
Livewire の画面をブラウザ更新したあとの復元には向きません。

| 操作 | 入力は残る？ |
| --- | --- |
| 「確認画面へ」→「修正する」 | 残る |
| ブラウザで更新（F5） | 消える |

今回は **F5 後も残す必要はない** という方針にしました。

### Q5. `gap-6` はどういう意味ですか？

Tailwind CSS のクラスで、**子要素どうしのすき間**です。

このフォームは縦並び（`flex flex-col`）なので、項目の上下が **24px** あきます。

| クラス | すき間 |
| --- | --- |
| `gap-4` | 16px |
| `gap-6` | 24px |
| `gap-8` | 32px |

### Q6. `validate` は必須という意味ですか？

**いいえ。`validate` は「チェックする」です。**

必須なのは、ルールの中の `required` です。

```php
'name' => ['required', 'string', 'max:255'],
'email' => ['required', 'string', 'email', 'max:255'],
```

| 言葉 | 意味 |
| --- | --- |
| `validate` | 入力がルールどおりか調べる |
| `required` | 空ならダメ（必須） |
| `email` | メール形式であること |
| `max:255` | 最大255文字まで |

### Q7. `ContactInquiry::create(...)` はデータベースに残すという意味ですか？

**はい、その理解で合っています。**

```php
$inquiry = ContactInquiry::query()->create($validated);
```

- `ContactInquiry` … そのテーブルを扱うクラス
- `create(...)` … 新しい行を作って保存する
- `$inquiry` … 保存した直後の1件（あとでメールに渡す）

---

## 5. 関連ファイル一覧

| ファイル | 役割 |
| --- | --- |
| `routes/web.php` | `/contact` への入り口 |
| `resources/views/pages/⚡contact.blade.php` | 入力・確認・送信の画面と処理 |
| `app/Models/ContactInquiry.php` | データベースに残す形 |
| `database/migrations/*_create_contact_inquiries_table.php` | テーブルを作る定義 |
| `app/Mail/ContactInquiryMail.php` | メールのひな形 |
| `resources/views/mail/contact-inquiry.blade.php` | メール本文の見た目 |
| `config/mail.php` の `contact_to` | 送り先アドレス |
| `tests/Feature/ContactInquiryTest.php` | 動きを確認するテスト |

---

## 6. いちばん短いまとめ

1. 画面で書く  
2. 確認する  
3. 送信する  
4. データベースに覚える  
5. メールのひな形にして送る  

**「書く場所」＋「覚えておく場所」＋「メールにして送る場所」**  
この3つがお問い合わせフォームです。
