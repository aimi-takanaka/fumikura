# マイページのタブ切り替えをやさしく理解する

文倉（fumikura）のマイページにある「自分の投稿」と「お気に入り一覧」のタブは、見た目はタブですが、中身は **別々のページ** です。この記事では、その仕組みを中学生でも分かるように説明します。

---

## 1. 結論：1枚の画面で中身を切り替えているわけではない

文倉のタブは、**見た目はタブだけど、中身は別々のページ** です。

| タブ | URL | 内容 |
| --- | --- | --- |
| 自分の投稿 | `/mypage` | 自分が書いた物語の一覧 |
| お気に入り一覧 | `/bookmarks` | お気に入りにした物語の一覧 |

タブを押すと、**そのページの URL に移動する** 仕組みです。  
本の「しおり」のように、**別のページを開くリンク** だと思ってください。

---

## 2. たとえ話でいうと

学校の掲示板に、こんな 2 枚の紙があるとします。

| 紙 | 住所（URL） | 内容 |
| --- | --- | --- |
| A | `/mypage` | 自分が書いた物語一覧 |
| B | `/bookmarks` | お気に入りにした物語一覧 |

タブは **「A の紙へ行く」「B の紙へ行く」ボタン** です。  
1 枚の紙の中で内容を入れ替えているわけではありません。

---

## 3. 仕組みを 3 ステップで

### ステップ 1：2 つのページを用意する

`routes/web.php` で、それぞれ別の URL を登録しています。

```php
Route::livewire('/bookmarks', 'pages::bookmarks')->name('bookmarks');
Route::livewire('/mypage', 'pages::mypage')->name('mypage');
```

- `/mypage` → マイページ（自分の投稿）
- `/bookmarks` → お気に入り一覧

### ステップ 2：両方のページに、同じタブを置く

タブ部分は `resources/views/partials/mypage-tabs.blade.php` という **共通パーツ** です。

マイページ（`resources/views/pages/⚡mypage.blade.php`）にも、お気に入りページ（`resources/views/pages/⚡bookmarks.blade.php`）にも、同じ部品を `@include` で差し込んでいます。

```blade
@include('partials.mypage-tabs')
```

だから、どちらのページを開いても、上に同じタブが見えます。

### ステップ 3：「今どのタブが選ばれているか」を URL で判断する

タブの共通パーツの中では、次のように **今開いているページの住所** を調べています。

```php
$isPosts = request()->routeIs('mypage');
$isBookmarks = request()->routeIs('bookmarks');
```

- `/mypage` にいる → 「自分の投稿」を朱色（`shu`）で強調
- `/bookmarks` にいる → 「お気に入り一覧」を朱色で強調

JavaScript で「タブ番号 = 1 なら A を表示」のような切り替えはしていません。  
**今いるページに合わせて、見た目だけ変えている** イメージです。

---

## 4. クリックしたときに何が起きるか

タブは `<a href="...">` という **普通のリンク** です。

```blade
<a href="{{ route('mypage') }}" wire:navigate>自分の投稿</a>
<a href="{{ route('bookmarks') }}" wire:navigate>お気に入り一覧</a>
```

クリックすると、次の流れになります。

1. ブラウザがその URL を開く
2. サーバー（Laravel）がそのページ用の HTML を作る
3. 画面がそのページの内容に変わる
4. 共通タブがまた表示され、今いる方だけ色付きになる

---

## 5. `wire:navigate` って何？

タブには `wire:navigate` という属性が付いています。

これは Livewire の機能で、**ページ全体をガツッと読み直さず、必要な部分だけ差し替える** イメージです。

| 方式 | 流れ |
| --- | --- |
| 普通のリンク | A → 全部読み直し → B |
| `wire:navigate` | A → 必要な部分だけ更新 → B（体感が軽い） |

操作は「別ページへ移動」ですが、**切り替えが少し速く・滑らか** に感じられます。

---

## 6. よくある「タブ」との違い

| 方式 | 文倉のやり方 | 別のやり方（参考） |
| --- | --- | --- |
| ページ数 | **2 ページ** | 1 ページのまま |
| 切り替え | **リンクで移動** | JavaScript で表示/非表示 |
| URL | `/mypage` と `/bookmarks` が変わる | URL は同じまま |
| データ | 各ページが自分用のデータを取得 | 1 ページ内で切り替え |

文倉は **シンプルで分かりやすい方式**（ページを分ける）を選んでいます。

メリットは次のとおりです。

- どのページにいるか URL で分かる
- ブックマークや共有がしやすい
- テスト（自動チェック）も書きやすい

---

## 7. 関連ファイル一覧

| ファイル | 役割 |
| --- | --- |
| `routes/web.php` | `/mypage` と `/bookmarks` の URL を定義 |
| `resources/views/partials/mypage-tabs.blade.php` | 2 つのタブリンク（共通パーツ） |
| `resources/views/pages/⚡mypage.blade.php` | 自分の投稿ページ |
| `resources/views/pages/⚡bookmarks.blade.php` | お気に入り一覧ページ |
| `tests/Feature/MyPageTest.php` | マイページのテスト |
| `tests/Feature/BookmarkListPageTest.php` | お気に入り一覧のテスト |

---

## 8. まとめ（1 文）

**「タブ風のリンクが 2 つあり、押すと別のページ（URL）に行き、今いるページに応じてどちらのタブが選ばれているか色が変わる」** — これが文倉のタブの仕組みです。
