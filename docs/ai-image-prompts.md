# 商品画像の作り方(AI 生成)

> 生成した画像を `seed/inbox/` に入れて変換スクリプトを実行し、そのあと `.\scripts\seed.ps1` を実行すると、商品にメイン画像が付きます。

## いま使っている画像

12 点すべてに AI で生成した写真(1122 × 1402 px、4:5)が入っています。共通のスタイル(クリームの背景、左からの夕日のような光、文字・ロゴなし)で、香りごとに素材を変えています(SALAR は塩の結晶、DUSK は夕日の光と石、CEDAR は杉の枝)。元画像が 1600px より小さいため、`prepare-images.py` は拡大せずにそのまま使います。商品詳細の大きな画像は `woocommerce_single_image_width` の 1000px で作られます。

最初にコードで描いたイラスト(`scripts/build-product-art.py`)は `seed/inbox/_illustration/` に保管しています。このスクリプトをもう一度実行すると `seed/inbox/` に同じ名前の PNG ができて写真と重なるため、実行する前に確認してください。

## 作業の流れ

1. AI で生成した画像を `seed/inbox/` に入れます。ファイル名は商品コード(SKU)で始めます(PNG / JPG / WebP のどれでもよく、大文字・小文字や `_` は区別しません)。
   - メイン画像: `KP-OIL-SALAR.png`
   - ギャラリー(任意): `KP-OIL-SALAR-2.png`、`KP-OIL-SALAR-3.png` …(商品詳細でサムネイルとして表示されます)
2. 変換: 4:5 に中央で切り抜き、横幅が 1600px より大きければ 1600×2000 に縮小して(小さければそのまま)、JPG(約 600KB 以下)で `seed/images/` に保存します。

```powershell
.\.venv-tools\Scripts\python scripts\prepare-images.py
```

   `.venv-tools` がなければ一度だけ作ります: `python -m venv .venv-tools` を実行し、続けて `.\.venv-tools\Scripts\python -m pip install pillow`

3. 反映: `.\scripts\seed.ps1` を実行すると商品に画像が付きます。画像を差し替えてもう一度実行すると、前の画像は削除されて新しい画像に置き換わります(同じ画像は重ねてアップロードしません)。
4. 商品カードは 4:5(縦長)、幅 600px で作られます(`seed-products.php` で設定)。この設定を変えたときは `.\scripts\wp.ps1 media regenerate --yes` でサムネイルを作り直します。

`seed/inbox/`(元画像)は Git に入れず、`seed/images/`(変換後)だけを入れます。

## 規格

| 項目 | 値 |
|---|---|
| 比率 / サイズ | 4:5 の縦長、1600 × 2000 px を推奨(最小 1000 × 1250。これより小さくても拡大しません) |
| 形式 | JPG(品質 85〜90、1 ファイル 500KB 前後まで小さくできると理想的) |
| ファイル名 | 商品コードそのまま: `KP-OIL-SALAR.jpg`(大文字、ハイフン) |
| 入れないもの | 実在するブランドのロゴ・商標、画像内の文字(AI は文字を崩しやすい)、人の顔 |

## 共通のスタイル(すべてのプロンプトの先頭に付ける)

```
Editorial product photograph, soft warm studio light from the left like late sunset,
seamless cream backdrop (#F4EEE4), subtle terracotta (#B07650) shadow tones,
minimal composition, generous negative space, shallow depth of field, matte surfaces,
no text, no logo, no brand name, no people, 4:5 vertical
```

## 商品ごとのプロンプト

| SKU | 商品 | プロンプト(共通のスタイルのあとに続ける) |
|---|---|---|
| KP-OIL-SALAR | SALAR 塩湖 オイルパフューム | a 10ml amber glass roll-on perfume bottle with a plain blank paper tag, standing on a thin sheet of white salt crystals, pale blue-pink reflection behind |
| KP-OIL-DUSK | DUSK 夕景 オイルパフューム | a 10ml dark amber glass roll-on bottle with a plain blank paper tag, on warm sand-colored stone, deep orange and plum glow at the horizon line |
| KP-OIL-CEDAR | CEDAR 杉 オイルパフューム | a 10ml amber glass roll-on bottle with a plain blank paper tag, next to a small cedar wood branch and moss, soft green-brown tones |
| KP-HAND-SALAR | SALAR 塩湖 ハンドパフューム | a 30ml matte white aluminum tube of hand cream, no print, lying on white salt crystals |
| KP-HAND-DUSK | DUSK 夕景 ハンドパフューム | a 30ml matte terracotta aluminum tube of hand cream, no print, on warm stone |
| KP-DIFF-SALAR | SALAR 塩湖 リードディフューザー | a 100ml clear frosted glass diffuser bottle with thin natural reed sticks, plain blank label, pale salt-white backdrop |
| KP-DIFF-CEDAR | CEDAR 杉 リードディフューザー | a 100ml dark green glass diffuser bottle with reed sticks, plain blank label, next to a cedar branch |
| KP-CAR-DUSK | DUSK 夕景 カーディフューザー | a small wooden-cap car vent diffuser clip, terracotta tone, on a dark leather surface |
| KP-CANDLE-SALAR | SALAR 塩湖 キャンドル | a 120g soy candle in a cream ceramic jar with matte lid, unlit, plain blank label, salt crystals around |
| KP-CANDLE-DUSK | DUSK 夕景 キャンドル | a 120g soy candle in a terracotta ceramic jar, lit with a small flame, warm glow on the wall |
| KP-GIFT-TRIAL | トライアルセット | three 3ml amber glass roll-on mini bottles lined up on a cream paper gift box, plain blank tags |
| KP-GIFT-SET | ギフトセット | a cream gift box open, containing a 10ml amber roll-on bottle and a small ceramic candle jar, kraft paper ribbon, plain blank labels |

## ラベルの扱い

AI で作るボトルには文字を入れません(上のプロンプトはすべて「blank label」)。Kapla のロゴや香りの名前は、あとから画像編集ツールで載せるか、サイトのデザイン(商品カードのテキスト)で見せます。

## ヒーロー動画

動画はすでに用意してあるため、ここで AI 画像は必要ありません。静止画(poster)は動画から 1 フレームを取り出して作ります。
