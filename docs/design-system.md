# デザインシステム

> 更新: 2026-10-28 ・ 方向性「A. Warm Editorial」: クリームの背景、テラコッタの差し色、明朝の見出し、細い線とたっぷりの余白。

## 目次

1. [フォント](#フォント)
2. [色](#色)
3. [部品](#部品)
4. [ページの構成](#ページの構成)
5. [動画と画像](#動画と画像)
6. [実装メモ](#実装メモ)

## フォント

すべて SIL Open Font License(OFL)で、`themes/kapla/assets/fonts/` から直接配信しています(入手元: `google/fonts`、ライセンスファイル `OFL-*.txt`)。

| 用途 | フォント | サイズ | 備考 |
|---|---|---|---|
| 本文 | Zen Kaku Gothic New 400 / 500 | 各 約 450KB | 太字は 500 だけを使う(`strong`、表の見出し、合計の行) |
| 見出し・メニュー | Shippori Mincho 500 | 約 690KB | ヘッダーのメニューは 16px、字間 0.14em |
| ロゴ・英字ラベル・価格・日付 | Cormorant Garamond 600 | 約 35KB | 英数字のみ。数字は `lining-nums`。ロゴの `KAPLA` は大文字と字間 0.3em を CSS で指定(サイト名は `Kapla`) |

- **サブセット**: ASCII、ラテン補助、記号、ひらがな、カタカナ、全角記号、JIS X 0208 の第 1 水準漢字(約 2,960 字)。サイトで使っている漢字 309 字はすべて含まれます。範囲外の文字は OS のフォントで表示されます。
- **表示**: すべて `font-display: swap` のため、読み込み前は OS のフォントで先に表示されます。本文には `font-feature-settings: "palt"` と `letter-spacing: 0.03em` を指定しています。
- **作り直し**: `python scripts/build-fonts.py <元フォントのフォルダー> themes/kapla/assets/fonts [logo|mincho|zen]`(fonttools、brotli が必要)

## 色

theme.json のパレットです。

| 名前 | 値 | 用途 |
|---|---|---|
| cream | `#F4EEE4` | ページの背景 |
| paper | `#FBF8F2` | カード・入力欄の背景 |
| sand / stone | `#E6DCCB` / `#CDBFA8` | 画像の背景、補助 |
| ink / ink-soft | `#2B211B` / `#6B5A4D` | 本文 / 補助の文字(コントラスト 5.7:1) |
| terracotta / terracotta-deep | `#B07650` / `#8A5636` | 差し色・ボタン・リンク(コントラスト 5.25:1) |
| night | `#1E1512` | フッター |

装飾の細い線は `#D9CCB6`、入力欄の枠線は `#9A8A78`(背景とのコントラスト 3:1 以上)。

## 部品

| 部品 | ルール |
|---|---|
| ボタン | 基本はテラコッタの塗り(マウスを乗せると ink)。商品一覧・おすすめ商品のカートボタンは細い枠線(マウスを乗せると塗りに変わる) |
| 入力欄 | 高さ 44px(ログインフォームは 48px)、枠線 1px、角丸 2px、フォーカス時はテラコッタのリング。WooCommerce のフォーム・レビュー・お問い合わせ(CF7)・カートと購入手続きのブロックで共通 |
| ログインフォーム | 中央のカード。ラベル・チェックボックス・リンクの文字は 14px にそろえ、ログインボタンと「ログイン状態を保存」を 1 行で縦中央にそろえる |
| 表 | しま模様なしで、上下の太い線と細い区切り線(特定商取引法に基づく表記、店舗)。スマートフォンでは縦に積む |
| アコーディオン | よくあるご質問。`core/details` ブロックと右側の +/− 表示 |
| ヘッダー | 左に日本語のテキストメニュー、中央にロゴ、右に細い線のアイコン(アカウント・カート)。メニューにマウスを乗せると下線が左から伸びる。トップページでは動画の上に透明で重なる |
| 商品カード | 4:5 の画像、明朝の商品名、Cormorant の価格。4 列 → 3 列(1023px 以下) → 2 列(719px 以下)。ショップ一覧・トップのおすすめ・関連商品・検索結果で同じ CSS を使う |
| カートに追加 | ボタンを押すと右からミニカートが開き、ボタンの文言は「追加済み（N点）」に変わる |
| 商品詳細 | 2 段組み(画像 \| 情報)、スマートフォンでは 1 段。拡大(ズーム)と虫めがねのボタンは使わない。タブは下線だけ |
| お知らせのバナー | クラシック(`.woocommerce-message`)とブロック(`.wc-block-components-notice-banner`)の両方で、細い枠線と左側のテラコッタの線 |

## ページの構成

- **ページの見出し**: `templates/page.html` は固定ページの上部に英字ラベル、タイトル、リード文を表示します。ラベルとリード文はカスタムフィールド `kapla_eyebrow`、`kapla_lead` から読み込み(空なら非表示)、初期データ投入(`seed-pages.php`)で値を入れます。管理画面では、ページ編集の「設定 > カスタムフィールド」から変更します。
- **ブランドページ**: `templates/page-brand.html`(スラッグ `brand`)は見出しなしで本文だけを表示します。本文は `patterns/brand-page.php` を初期データ投入でページの内容として保存したものなので、管理画面から直接編集できます。香りのカードは CSS のグラデーション(`.kapla-swatch--salar|dusk|cedar`)です。
- **ファビコン**: `themes/kapla/assets/images/favicon.png`(512×512、濃い茶色の背景にクリーム色の「K」)を `scripts/seed-branding.php` がサイトアイコンに設定します。作り直し: `python scripts/build-favicon.py`

## 動画と画像

| ファイル | 内容 |
|---|---|
| `themes/kapla/assets/video/hero.mp4` | トップのヒーロー動画(約 1.4MB、1080p、音声なし、H.264) |
| `themes/kapla/assets/video/hero-poster.jpg` | 動画の読み込み中や、動きを減らす設定のときに表示する静止画(約 70KB) |
| `themes/kapla/assets/images/og-image.jpg` | OGP(SNS での共有)用の画像 1200×630 |
| `seed/images/<SKU>.jpg` | 商品写真 12 点(AI で生成、4:5) |

- ヒーロー動画は、著作権の問題なく使える元動画を圧縮したものです。元動画は Git に入れていません。作り直し: `.\scripts\build-video.ps1 -Ffmpeg <ffmpeg.exe のパス>`
- 商品写真は `seed/images/<SKU>.jpg` に置いて `.\scripts\seed.ps1` を実行すると商品に付きます。規格とプロンプトは [ai-image-prompts.md](ai-image-prompts.md)。実際の商品写真に差し替えるときも、比率(4:5)と背景色(sand)はそのまま使えます。

## 実装メモ

- **商品詳細・一覧・検索はブロックテンプレート**です(`templates/single-product.html`、`archive-product.html`、`search.html`)。ギャラリー・カートのフォーム・タブの内側の HTML は、WooCommerce のブロックがクラシックと同じマークアップで出力するため、`form.cart` や `.woocommerce-tabs` などのクラスはそのまま使います。外側の `div.product` はないので、セレクターは `.kapla-product …` です。
- 商品コードの表示は、`product-sku` ブロックの `prefix` 属性で「商品コード: 」を付けています。
- 関連商品のパターンは、コアが `is-flex-container columns-5` の `li` の幅を 20% で計算するため、グリッドに任せるようにセレクターを詳しく書いて上書きしています。
- WooCommerce 標準の CSS は優先度が高いため、商品一覧・詳細・タブのルールは `body.woocommerce …` のようにセレクターを詳しく書いています。
- WooCommerce はブロックテンプレートで商品画像のズームを強制的に有効にするため、`functions.php` のズームを無効にするフィルターは優先度 999 で登録しています。
- スタイルシートは `style.css` 1 つ(セクション 0〜13)で、バージョンは 0.4.13。ブラウザーが古い CSS を覚えているときは強制再読み込み(Ctrl+F5)をします。
