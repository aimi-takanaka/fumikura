# `Story::complete()` と `Story::factory()->complete()` の違いをやさしく理解する

文倉（fumikura）のコードやテストを読むと、`complete()` という名前が2種類出てきます。

- `Story::complete()`
- `Story::factory()->complete()`

**名前は同じ `complete()` ですが、役割はまったく違います。**

この記事では、その違いを中学生でも分かるように説明します。

---

## 1. 結論：2つの `complete()` がある

| | `Story::complete()` | `Story::factory()->complete()` |
| --- | --- | --- |
| **何をする？** | すでにあるデータから **完成だけ探す** | テスト用のデータを **完成として新しく作る** |
| **たとえ** | 図書館で「完成ラベルの本だけ探す」 | テスト用に「完成ラベル付きの見本本を1冊作る」 |
| **定義場所** | `app/Models/Story.php` | `database/factories/StoryFactory.php` |
| **主に使う場所** | 本番の画面・検索 | テスト |

---

## 2. `Story::complete()` … **探す・絞る**

### 定義場所

`app/Models/Story.php` に書いてあります。

```php
public function scopeComplete(Builder $query): void
{
    $query->where('status', StoryStatus::Complete);
}
```

### 何をしている？

**データベースの中から**、「ステータスが完成のストーリーだけ」を取り出す **フィルター** です。

```php
Story::complete()->count();  // 完成は何件？
```

### たとえ

図書館の本棚から、**「完成」シールが付いた本だけ** を取り出すイメージです。

### 文倉での使用例

お題一覧で「完成2件」と数えるときなどに使います。

`app/Livewire/Concerns/ListsTopics.php` より:

```php
'stories as completed_stories_count' => fn (Builder $query) => $query->complete(),
```

ここでは `Story::complete()->get()` という形ではなく、`$query->complete()` と **クエリの途中で** 絞り込んでいます。

---

## 3. `Story::factory()->complete()` … **作る（テスト用）**

### 定義場所

`database/factories/StoryFactory.php` に書いてあります。

```php
public function complete(): static
{
    return $this->state(fn (array $attributes) => [
        'status' => StoryStatus::Complete,
    ]);
}
```

### 何をしている？

**新しいストーリーを1件作る準備** のときに、「この1件は完成ステータスで作ってね」と指定する部分です。

```php
Story::factory()->complete()->create(['title' => '完成した物語']);
```

| 部分 | 意味 |
| --- | --- |
| `factory()` | テスト用データを作る工場 |
| `complete()` | 「完成で作って」という指定 |
| `create()` | 実際に DB に保存 |

**探すのではなく、作る** ためのコードです。

---

## 4. なぜ名前が同じなの？

どちらも **「完成（complete）に関係する処理」** だから、同じ `complete()` という名前が付いています。

でも **付いている場所（クラス）が違う** ので、意味が変わります。

```
Story::complete()              ← Story モデルの scope（探す）
Story::factory()->complete()   ← StoryFactory の state（作る）
       ↑
    ここで別クラスに切り替わる
```

---

## 5. テストで両方出てくる例

`tests/Feature/ModelRelationshipsTest.php` には、**両方が1つのテストの中** に出てきます。

```php
// ① ファクトリ：完成2件 + アイデア2件 + 下書き3件を「作る」
Story::factory()->complete()->count(2)->create();
Story::factory()->idea()->count(2)->create();
Story::factory()->draft()->count(3)->create();

// ② スコープ：完成だけが2件取れるか「探して」確認
expect(Story::complete()->count())->toBe(2);
```

### 流れ

1. **factory** で色々なステータスのデータを作る（合計7件）
2. **scope** で完成だけ数える → 2件になるはず、とテスト

---

## 6. たとえ話：学校の提出物

### `Story::factory()->complete()` = 見本を作る

テストのために、先生が **「完成」シールを貼った見本の提出物** を机に置く。

- 実際の授業用というより **テスト準備**
- 「この1枚は完成として作っておいて」と指定する

### `Story::complete()` = 提出物から探す

ロッカーに並んでいる提出物の中から、**「完成」シールの付いたものだけ** を取り出す。

- すでにあるものを **探す・数える**
- 本番の画面（お題一覧など）で使うイメージ

---

## 7. 見分け方（チェックリスト）

コードを見たとき、次で判断できます。

| こう書いてあったら | 意味 |
| --- | --- |
| `Story::complete()` | 探す・絞る（スコープ） |
| `Story::factory()` が前にある | 作る（ファクトリ） |
| `->create()` が後にある | たいていファクトリ |
| `->count()` / `->get()` だけ | たいていスコープ |

---

## 8. 補足：`scope` は省略されている

`Story.php` では **`scopeComplete`** と書きますが、使うときは **`scope` を付けません**。

| 定義（Story.php） | 使うとき |
| --- | --- |
| `scopeComplete` | `->complete()` |

---

## 9. 補足：`idea()` や `draft()` も同じ

ファクトリ側には、同じパターンで `idea()` や `draft()` もあります。

```php
Story::factory()->idea()->create();   // アイデアの見本データを作る
Story::factory()->draft()->create();  // 下書きの見本データを作る
```

テストで `Story::factory()->draft()->create()` と書いてあっても、それは **下書きの見本データを作っている** だけで、**下書きだけを検索しているわけではありません**。

---

## 10. 関連ファイル一覧

| ファイル | 役割 |
| --- | --- |
| `app/Models/Story.php` | `scopeComplete` … 完成だけ探す |
| `database/factories/StoryFactory.php` | `complete()` … 完成のテストデータを作る |
| `app/Livewire/Concerns/ListsTopics.php` | 本番で `$query->complete()` を使用 |
| `tests/Feature/ModelRelationshipsTest.php` | 両方を使ったテスト例 |

---

## 11. まとめ（1文）

**`Story::factory()->complete()` は「完成ステータスのテストデータを作る」、`Story::complete()` は「DB から完成だけ探す」** — 名前は同じ `complete()` ですが、**作る側** と **探す側** で別物です。
