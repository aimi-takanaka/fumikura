# ストーリー入力の一時保存をやさしく振り返る

文倉（fumikura）のストーリー投稿・編集画面で、**リロードしても書いた内容が残る**ようにしたときの振り返りです。  
中学生やプログラミング初心者でも読めるように書いています。

---

## 1. 何ができるようになったか

ストーリーを書いている途中で、うっかりページをリロード（再読み込み）しても、タイトル・本文・ステータスが戻ってきます。

| できること | できないこと |
| --- | --- |
| 同じブラウザで、リロード後に入力内容を復元する | 別のスマホ・別PCへ自動で引き継ぐ |
| 「下書き」ボタンを押さなくても自動で一時保存する | サーバ（データベース）に下書きとして保存する |

画面には、次のような案内も出ます。

> 入力内容はこのブラウザに自動で一時保存されます（リロードしても復元できます）。

---

## 2. 結論：やっていることはとてもシンプル

やっていることは、ざっくり4つだけです。

```text
1. 入力内容に「引き出しの名前」をつける
2. 文字を打つと、ブラウザの引き出しにメモする
3. ページを開き直したら、メモをフォームに戻す
4. 投稿（または更新）に成功したら、メモを捨てる
```

たとえ話にするとこうです。

| 役割 | たとえ | このプロジェクトでの場所 |
| --- | --- | --- |
| 引き出しの名前 | 「お題5番・ユーザー2番のメモ」というラベル | `browserDraftKey()` |
| メモ係 | 書いた内容を引き出しにしまう係 | `storyComposerDraft`（`app.js`） |
| 引き出し本体 | ブラウザの中にある小さなメモ帳 | `localStorage` |
| 投稿後の掃除 | 用が済んだメモを捨てる | `save()` / `update()` の中の `localStorage.removeItem` |

**データベースの下書き保存は使っていません。**  
自分のPCのブラウザの中だけに、一時メモを置いています。

---

## 3. なぜ DB の下書きじゃなくてよいのか

| 方法 | 向いていること | むずかしさ |
| --- | --- | --- |
| ブラウザ一時保存（今回） | うっかりリロードへの対策 | かんたん |
| DBの下書き（ステータスを下書きにして投稿） | 別端末でも続きを書く・長く保管する | 操作が必要 |

今回の目的は「リロードで消えないこと」なので、ブラウザ保存で十分です。

---

## 4. 実際のコードで見る流れ

ここでは **新規投稿画面**（`resources/views/pages/stories/⚡create.blade.php`）を例にします。  
編集画面も同じ仕組みです（引き出しの名前だけ違います）。

### 4-1. 引き出しの名前を決める

```php
public function browserDraftKey(): string
{
    return 'fumikura.story-create.'.$this->image->id.'.u'.auth()->id();
}
```

例えばお題 ID が `5`、ユーザー ID が `2` なら、名前は次のようになります。

```text
fumikura.story-create.5.u2
```

名前を分けることで、別のお題や別ユーザーのメモと混ざりません。

編集画面では、だいたい次のような名前になります。

```text
fumikura.story-edit.{ストーリーID}.u{ユーザーID}
```

### 4-2. 画面に「メモ係」をくっつける

```blade
<div
    class="mx-auto max-w-2xl"
    x-data="storyComposerDraft({
        key: @js($this->browserDraftKey()),
        skipRestore: @js($skipBrowserDraftRestore),
    })"
>
```

| ことば | 意味 |
| --- | --- |
| `x-data` | この画面パーツに Alpine.js の係を起動する |
| `storyComposerDraft` | 一時保存係の名前（`app.js` で定義） |
| `key` | 引き出しの名前 |
| `skipRestore` | たまに「メモから戻さない」とき用のフラグ |

`skipRestore` が `true` になる例は、アナザーストーリー案をフォームに入れた直後です。  
そのときはサーバ側の下書きを優先し、古いブラウザメモで上書きしないようにしています。

### 4-3. ページを開いたときの仕事

`resources/js/app.js` の `init()` です。

```js
init() {
    if (! this.key) {
        return;
    }

    if (! this.skipRestore) {
        this.restore();
    }

    this.$wire.$watch('title', () => this.scheduleSave());
    this.$wire.$watch('body', () => this.scheduleSave());
    this.$wire.$watch('status', () => this.scheduleSave());
},
```

やっていること:

1. メモがあれば `restore()` でフォームに戻す  
2. タイトル・本文・ステータスが変わったら「保存の予約」をする  

`$wire` は、Livewire が見ているフォームの中身（今のタイトルや本文）です。

### 4-4. 入力されたらメモする

```js
scheduleSave() {
    clearTimeout(this.timer);
    this.timer = setTimeout(() => this.persist(), 400);
},

persist() {
    const title = this.$wire.title ?? '';
    const body = this.$wire.body ?? '';
    const status = this.$wire.status ?? '';

    if (title === '' && body === '') {
        localStorage.removeItem(this.key);
        return;
    }

    localStorage.setItem(this.key, JSON.stringify({
        title,
        body,
        status,
        savedAt: Date.now(),
    }));
},
```

| 関数 | 役割 |
| --- | --- |
| `scheduleSave` | 入力のたびにすぐ書かず、**0.4秒静かになってから**保存する |
| `persist` | 本当に `localStorage` へ書き込む |
| `localStorage.setItem` | ブラウザの引き出しに保存する |
| `localStorage.removeItem` | 引き出しのメモを消す |

保存される中身のイメージ:

```json
{
  "title": "灯台の夜",
  "body": "その夜、光は…",
  "status": "draft",
  "savedAt": 1730000000000
}
```

タイトルも本文も空なら、メモは残さず消します。

### 4-5. リロード後に戻す

```js
restore() {
    const raw = localStorage.getItem(this.key);

    if (! raw) {
        return;
    }

    try {
        const data = JSON.parse(raw);

        if (typeof data.title === 'string') {
            this.$wire.title = data.title;
        }

        if (typeof data.body === 'string') {
            this.$wire.body = data.body;
        }

        if (typeof data.status === 'string' && data.status !== '') {
            this.$wire.status = data.status;
        }
    } catch {
        localStorage.removeItem(this.key);
    }
},
```

やっていること:

1. `localStorage.getItem` でメモを取り出す  
2. JSON 文字列を普通のデータに戻す（`JSON.parse`）  
3. `$wire.title` などに入れて、画面の入力欄へ反映する  

壊れたメモ（パースできないデータ）は、`catch` で消します。

### 4-6. 投稿成功したらメモを捨てる

```php
$this->js('localStorage.removeItem('.json_encode($this->browserDraftKey()).')');
```

データベースにストーリーを保存したあと、ブラウザへ「このキーのメモを消して」と命令しています。  
古い下書きがずっと残って邪魔しないようにするためです。

編集画面の `update()` でも、同じように消しています。

---

## 5. 全体の流れ（図）

```text
入力する
  → $watch が気づく
  → 0.4秒後に localStorage へ保存

リロードする
  → init → restore
  → localStorage からフォームへ戻す

投稿する / 更新する
  → DB に保存
  → localStorage のメモを削除
```

---

## 6. 関係するファイル一覧

| ファイル | 役割 |
| --- | --- |
| `resources/js/app.js` | `storyComposerDraft`（保存・復元の本体） |
| `resources/views/pages/stories/⚡create.blade.php` | 新規投稿画面。キー生成・係の起動・投稿後の削除 |
| `resources/views/pages/stories/⚡edit.blade.php` | 編集画面。同様の仕組み |
| `tests/Feature/StoryCreateTest.php` | 画面に一時保存の案内やキーが出ることの確認 |
| `tests/Feature/StoryEditTest.php` | 編集画面側の同様の確認 |

---

## 7. 学習のヒント：出てきたことば

| ことば | やさしい意味 |
| --- | --- |
| `localStorage` | ブラウザの中にある小さなメモ帳（ページを閉じても残る） |
| `JSON` | データを文字として並べる書き方（メモの中身の形） |
| `Alpine.js` | 画面の小さな動きを書くための道具 |
| `Livewire` / `$wire` | PHP 側と画面の入力をつなぐ仕組み |
| `debounce`（0.4秒待ち） | 連打入力のたびに動かず、少し待ってからまとめて動かす工夫 |

---

## 8. よくある質問

### Q. 下書きステータスを押さないと残らないの？

A. いいえ。入力しているだけでブラウザに一時保存されます。  
下書きステータスは「作品の公開状態」の話で、今回の一時保存とは別物です。

### Q. 別のパソコンでも戻る？

A. 戻りません。メモは **そのブラウザの中** にあります。  
別端末でも続きを書きたいときは、下書きとして投稿して DB に残す方法が向いています。

### Q. 本番に反映するとき注意することは？

A. `app.js` を変えているので、`npm run build` などでフロントのビルド成果物（`public/build`）も一緒に反映する必要があります。PHP の Blade だけ `git pull` しても、JS の係が古いと動きません。

---

## 9. 一言でまとめると

> **書いた内容を、ブラウザの引き出しに自動メモして、開き直したら戻す。**  
> 投稿できたらメモは捨てる。

これだけで、うっかりリロードへの対策ができています。
