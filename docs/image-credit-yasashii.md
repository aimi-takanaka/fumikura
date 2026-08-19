# お題画像の作者クレジットをやさしく振り返る

文倉（fumikura）のお題画像に、**作者名・サービス名・リンク付きクレジット**を付けられるようにしたときの振り返りです。  
中学生やプログラミング初心者でも読めるように書いています。

公開ブログ向けに、サーバー接続情報・秘密鍵・本番URLなど、公開しない方がよい詳細は書いていません。

---

## 1. 何ができるようになったか

お題画像のタイトルの下・投稿者の上に、次のようなクレジットを出せます。

| 種別 | 画面の見え方（例） |
| --- | --- |
| イラスト | `イラスト：Yamada Taro　Example Art` |
| 写真 | `写真：Sato Hanako　Unsplash` |
| 未選択 | 何も出さない |

サービス名には URL を付けられます。URL があるときは、サービス名がリンクになり（色・下線・外部リンクのアイコン付き）、別タブで開きます。

管理者は次の2か所から入力できます。

- **新規投稿** … お題画像を上げるとき
- **編集** … すでに投稿済みのお題にも、あとから作者を追加できる

---

## 2. 結論：やっていることはとてもシンプル

やっていることは、ざっくり3つだけです。

```text
1. 管理者が「種別・作者・サービス・URL」を入力する
2. データベースに保存する
3. お題ページで、種別があるときだけ表示する
```

たとえ話にするとこうです。

| 役割 | たとえ | この機能での場所 |
| --- | --- | --- |
| 引き出し | 何を覚えさせるか | DB のカラム（種別・作者・サービス・URL） |
| 対応表 | 英語の記号 → 日本語の見出し | `ImageCreditType`（`illustration` → `イラスト：`） |
| 受付係 | 入力チェックして保存 | 管理画面の `save()` |
| 判定係 | 「出してよいか？」 | `Image` モデルの `hasCredit()` / `creditLine()` |
| 掲示板 | 実際に見せる | お題詳細の Blade |

---

## 3. なぜクレジットが必要か

お題に使う絵や写真には、**作者や提供元をきちんと示す**必要があることが多いです。

以前はタイトルと投稿者（アップロードした人）だけでした。  
今回は「画像そのものの作者・サービス」を、タイトル直下に分けて出せるようにしました。

英語の `illustration by` ではなく、文倉の雰囲気に合わせて日本語にしています。

```text
illustration by ...  →  イラスト：...
photo by ...         →  写真：...
```

コロンも、日本語表記に合わせて全角の `：` にそろえています（`投稿者：` も同様）。

---

## 4. データの流れ（いちばん大事）

```text
管理者が入力
  ↓
バリデーション（変な入力を止める）
  ↓
データベースに保存
  ↓
お題ページを開く
  ↓
種別があるときだけクレジットを表示
```

**画面で見えているもの**と**中にしまってあるデータ**は別です。  
「入力 → 保存 → 表示」の3段階だと覚えると迷いにくいです。

---

## 5. 保存する項目（引き出しの中身）

お題画像（`images`）に、だいたい次の情報を足しています。

| 項目 | 意味 | 空でもよいか |
| --- | --- | --- |
| クレジット種別 | イラストか写真か | よい（未選択＝表示なし） |
| 作者名 | 誰が描いた／撮ったか | 種別ありのときは必須 |
| サービス名 | どこから来たか | 種別ありのときは必須 |
| サービスURL | サービスへのリンク | 種別ありのときは必須 |

マイグレーション（テーブル変更の設計図）では、イメージとしてこうなります。

```php
$table->string('credit_type')->nullable();
$table->string('credit_author')->nullable();
$table->string('credit_service')->nullable();
$table->string('credit_service_url')->nullable();
```

`nullable()` は「空でもOK」という意味です。  
すでにあったお題にはクレジットがないので、空を許す必要があります。

---

## 6. 種別の対応表（Enum）

DB には短い英語（`illustration` / `photo`）を保存し、画面では日本語にします。

```php
enum ImageCreditType: string
{
    case Illustration = 'illustration';
    case Photo = 'photo';

    public function creditPrefix(): string
    {
        return match ($this) {
            self::Illustration => 'イラスト：',
            self::Photo => '写真：',
        };
    }
}
```

| DBに入る値 | 画面に出す文字 |
| --- | --- |
| `illustration` | `イラスト：` |
| `photo` | `写真：` |

**覚えどころ:** 「見せる文言を変えたい」ときは、だいたいこの対応表を直します。

---

## 7. 保存するときのルール（管理画面）

編集・新規ともに、保存前にチェックします。

```php
'creditType' => ['nullable', Rule::enum(ImageCreditType::class)],
'creditAuthor' => ['required_with:creditType', ...],
'creditService' => ['required_with:creditType', ...],
'creditServiceUrl' => ['required_with:creditType', 'url', ...],
```

ポイントは `required_with:creditType` です。

- 種別が **未選択** → 作者などは書かなくてよい
- 種別が **あり** → 作者・サービス・URL が必須

保存時は、種別がなければ作者なども `null` にします。

```php
'credit_author' => $creditType ? $validated['creditAuthor'] : null,
'credit_service' => $creditType ? $validated['creditService'] : null,
'credit_service_url' => $creditType ? $validated['creditServiceUrl'] : null,
```

これが **「未選択なら表記なし」** の本体です。

---

## 8. 「出してよいか？」を決める（モデル）

画面側に長い判定を書きすぎないよう、`Image` モデルに寄せています。

```php
public function creditLine(): ?string
{
    if ($this->credit_type === null || $this->credit_author === null || $this->credit_service === null) {
        return null;
    }

    $author = trim($this->credit_author);
    $service = trim($this->credit_service);

    if ($author === '' || $service === '') {
        return null;
    }

    return $this->credit_type->creditPrefix().$author.'　'.$service;
}
```

例:

- 種別あり・作者 `Sato`・サービス `Unsplash`  
  → `写真：Sato　Unsplash`
- 種別なし  
  → `null`（画面に出さない）

`hasCredit()` は「`creditLine()` があるか？」を見るスイッチです。  
URL があるかどうかは `creditServiceUrl()` で見ます。

---

## 9. 画面での出し方（お題詳細）

ざっくりこうです。

```blade
@if ($image->hasCredit())
    <p>
        {{-- イラスト：作者名　 --}}
        {{ $image->credit_type->creditPrefix() }}{{ $image->credit_author }}　

        @if ($image->creditServiceUrl())
            <a href="..." target="_blank" rel="noopener noreferrer">
                {{ $image->credit_service }}
                {{-- 外部リンクだと分かるアイコン --}}
            </a>
        @else
            {{ $image->credit_service }}
        @endif
    </p>
@endif

<p>投稿者：{{ $image->user?->name ?? '不明' }}</p>
```

読める順番:

1. クレジットがあるときだけ枠を出す  
2. `イラスト：` または `写真：` ＋ 作者名  
3. URL あり → サービス名をリンクに  
4. URL なし → サービス名を普通の文字で  
5. その下に投稿者（アップロードした人）

リンクは「薄い色の普通の文字」だと気づきにくいので、**色・下線・外部リンクアイコン**で「押せる」と分かるようにしています。

---

## 10. 新規と編集は別ページ

| 画面 | 役割 |
| --- | --- |
| お題画像の投稿 | 新しいお題を作るときにクレジットも入れる |
| お題画像の編集 | 既存のお題に、あとから作者を足す／直す |

「すでに投稿済みにも作者を追加したい」は、編集ページの仕事です。  
お題詳細では、管理者だけに「編集」リンクを出しています。

---

## 11. 困ったときの探し方

| やりたいこと | 見る場所のイメージ |
| --- | --- |
| `イラスト：` の文字を変えたい | 種別の Enum（`creditPrefix()`） |
| リンクの見た目を変えたい | お題詳細の Blade |
| 入力項目を増やしたい | マイグレーション → モデル → 管理画面の保存 → 表示 |
| 既存画像に作者を足したい | お題詳細の「編集」 |

---

## 12. 初心者が覚えやすい「5つの約束」

1. **データはDB、見た目は Blade**  
   文言変更と、保存項目の追加は場所が違う。

2. **未選択＝何も出さない**  
   種別が空なら、クレジット行自体を出さない。

3. **リンクは「名前」と「URL」が別**  
   サービス名は表示文字、URL は飛び先。

4. **新規と編集は別**  
   あとから足すなら編集画面。

5. **テストで守る**  
   「保存できるか」「画面に出るか」「未選択なら出ないか」を自動テストで確認している。

---

## 13. 練習問題（理解チェック）

1. 「イラスト：」を「挿絵：」に変えたい → どこを直す？  
   → 種別 Enum の `creditPrefix()`

2. 新しい保存項目を足したい → どこから手を付ける？  
   → マイグレーション → モデル → 管理画面の `save()` → お題詳細の表示

3. なぜ未選択だと何も出ない？  
   → 保存時に `null` にし、モデルも `null` を返すから

この3つが言えれば、この機能の骨格は押さえられています。

---

## 14. 一言まとめ

**「DB に種別・作者・サービス・URL を保存し、種別があるときだけお題ページで日本語クレジットを出す」** 機能です。

入力と表示を分け、未選択をきちんと空にすることが、いちばん大事な設計ポイントでした。
