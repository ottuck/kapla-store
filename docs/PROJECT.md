# プロジェクト記録 ― 設定・公開・運用

> 更新: 2026-10-28 ・ 決済・配送、お問い合わせフォームと SEO、公開環境(自宅サーバー)、納品用ホスティングの調査をまとめた文書です。デザインは [design-system.md](design-system.md)、商品写真は [ai-image-prompts.md](ai-image-prompts.md) を参照してください。

## 目次

1. [現在の状態](#1-現在の状態)
2. [決済・配送](#2-決済配送)
3. [お問い合わせフォームと SEO](#3-お問い合わせフォームと-seo)
4. [公開環境: 自宅サーバー](#4-公開環境-自宅サーバー)
5. [納品用ホスティングの事前調査](#5-納品用ホスティングの事前調査)

## 1. 現在の状態

| 項目 | 状態 |
|---|---|
| 公開アドレス | https://kapla.portfolio-hub.dev (自宅サーバーのため、停止している日があります) |
| バージョン | WordPress 7.1.3、WooCommerce 11.2.1、PHP 8.5.12、MySQL 8.4 |
| 通貨・税 | JPY(小数点なし)、税込で入力・表示、消費税 10% |
| 決済 | Stripe テストモード(カード)、代金引換。実際の決済は行いません |
| 配送 | 日本国内のみ。全国一律 500 円、8,000 円(税込)以上は無料 |
| 検索エンジン | デモ期間中は `noindex, nofollow` |

**範囲はポートフォリオ用のデモです。** SMTP によるメール送信、バックアップと復元のリハーサル、定期 cron、Cloudflare のキャッシュルール・Access による保護、Stripe Webhook は行わないことにしました。実際の運用が必要になったときは [4.6](#46-必要になったときの参考いまは行わない) を参照します。

## 2. 決済・配送

### 2.1 設定

| 項目 | 内容 |
|---|---|
| 配送地域 | `日本` のゾーン 1 つ |
| 送料 | 全国一律 500 円(デモでは非課税扱いにして、ちょうど 500 円と表示)。8,000 円(税込)以上は無料で、無料の条件を満たすと有料の送料は選択肢から外れます |
| 代金引換 | 有効。Stripe がなくても注文の流れを確認できます |
| Stripe | `woocommerce-gateway-stripe` 11.1.1、テストキー(サンドボックス、JPY) |
| 購入手続き | ブロック版。日本の住所入力欄(姓・名、郵便番号、都道府県、市区町村、住所) |

日本向けの調整:

- 支払い方法の名前を `Credit / Debit Card` から `クレジットカード / デビットカード` に変更(`plugins/kapla-shop-tweaks` の gettext フィルター)
- Stripe Link、Apple Pay、Google Pay、エクスプレスチェックアウトのボタンは無効。Link などの支払い方法の設定は Stripe アカウント側に保存されるため、Git には残りません
- 保存済みカード(`saved_cards`)を無効にして、カード保存の案内文を出さないようにしました
- サンドボックスの初期設定で有効になっている海外の支払い方法(Bancontact など)は、JPY・日本の注文では表示されないことを確認しました(カードのみ表示)

送料と決済の設定は `scripts/seed-checkout.php` で作り直せます。送料無料のときに有料の送料を隠す処理は `plugins/kapla-shop-tweaks` にあります(テーマには注文のロジックを入れていません)。

### 2.2 確認した注文の流れ(ローカル)

| 支払い | 注文内容 | 結果 |
|---|---|---|
| 代金引換 | SALAR オイルパフューム ¥5,500 | 合計 ¥6,000(送料 ¥500、消費税 ¥500 を含む)、状態 `processing`、完了画面は日本語で表示 |
| 代金引換 | ギフトセット ¥8,800 | 無料の送料だけが表示され、合計 ¥8,800 |
| Stripe テストカード | ギフトセット + DUSK オイルパフューム | 送料無料、合計 ¥14,300(消費税 ¥1,300 を含む)、決済完了、`livemode=false` |

- ローカル(HTTP、Webhook なし)でも、カード決済から注文確定まで動作しました。
- テスト注文とテスト用の顧客データは公開前に削除しました(2026-10-26)。

### 2.3 Stripe のテストキーを入れる

秘密鍵はチャットや Git に残しません。キーはデータベース(`woocommerce_stripe_settings`)にだけ保存されます。

1. [Stripe](https://dashboard.stripe.com/register) でアカウントを作り、**テストモード**に切り替えます。
2. 開発者 → API キー で `pk_test_…` と `sk_test_…` を確認します。
3. 次のコマンドでキーを入力します。入力内容は画面に表示されず、`pk_test_` / `sk_test_` のキーだけを受け付けます。

```powershell
.\scripts\stripe-test-keys.ps1
```

4. 購入手続きの画面で、テストカード `4242 4242 4242 4242`(有効期限は未来の任意の日付、CVC は任意の数字)で注文します。

## 3. お問い合わせフォームと SEO

### 3.1 プラグイン

| プラグイン | バージョン | 備考 |
|---|---|---|
| Contact Form 7 | 6.2.3 | 日本語の翻訳をインストール(`wp language plugin install contact-form-7 ja`) |
| SEO SIMPLE PACK | 3.7.0 | PHP 8.5.12 でエラーなし |

### 3.2 お問い合わせフォーム(`scripts/seed-contact.php`)

- タイトルは `お問い合わせ`。項目: お名前(必須)、メールアドレス(必須)、お問い合わせの種類(選択)、お問い合わせ内容(必須)、個人情報の取り扱いへの同意(必須)、送信する。
- お問い合わせページ(`/contact/`)にブロックで設置しています。入力欄のスタイルは `style.css` の `.wpcf7` のルールです。
- メールは管理者宛てと送信者への自動返信の 2 通です。
- スパム対策: 人には見えない入力欄 `kp_website`(ハニーポット)に値が入っていると、スパムとして扱います(`plugins/kapla-shop-tweaks`)。

ローカルで確認したこと:

- 同意にチェックして送信すると完了メッセージが表示され、Local の Mailpit に管理者宛てメールと自動返信が届きました。注文メール(管理者・お客様)も届きました。
- ハニーポットの欄に値を入れた送信は `spam` として止まり、メールは送られませんでした。
- 同意のチェックボックスは、人が実際にクリックしないと送信ボタンが有効になりません。

> 公開環境にはメールを送る仕組みがないため、**お問い合わせ・注文のメールは送信されません**(SMTP は範囲外)。

### 3.3 SEO(`scripts/seed-seo.php`)

- トップページに `<title>`、`description`、canonical、OGP(`og:*`)、`twitter:card` を出力しています。説明文は日本語です。
- WordPress 標準のサイトマップ `/wp-sitemap.xml`。
- デモ期間中は `noindex, nofollow`(`blog_public = 0`)です。検索結果に出すときは、設定 → 表示設定 の「検索エンジンがサイトをインデックスしないようにする」をオフにします。

## 4. 公開環境: 自宅サーバー

ローカル(LocalWP)で作ったサイトを M1 MacBook Air の Docker Compose に移し、Cloudflare Tunnel で公開しています。配置ファイルは `infra/home-server/` にあります。

```
訪問者 → Cloudflare(DNS・HTTPS) → Tunnel → 127.0.0.1:8081 → wordpress(Apache、PHP 8.5) → db(MySQL 8.4)
```

| ファイル | 説明 |
|---|---|
| `infra/home-server/compose.home.yaml` | `wordpress`、`db`、`wpcli`(tools プロファイル)。PHP と Apache の設定はファイル内の `configs` にあります |
| `infra/home-server/.env.example` | 変数の一覧。`.env` にコピーして値を入れます(`.env` は Git に入れません) |

### 4.1 設計のポイント

- **イメージ**: ローカルと同じ `wordpress:7.1.3-php8.5-apache` と `mysql:8.4`(どちらも arm64 版あり)。
- **ポート**: `127.0.0.1:8081` だけを開き、データベースのポートは開けません。外部への公開は Tunnel のみで、ルーターのポートも開けていません。
- **ボリューム**: `kapla-wp`(コア・プラグイン・uploads)と `kapla-db` の 2 つ。テーマと `kapla-shop-tweaks` は Git リポジトリを読み取り専用で重ねてマウントするため、**コードは Git でだけ変更します**。
- **メモリ上限**: wordpress 512m、db 512m、wpcli 256m。Apache の同時処理数(MaxRequestWorkers)は 6。実際のアイドル時のメモリは wordpress 約 120MB、db 約 230MB です。
- **HTTPS**: 公式イメージの wp-config が `X-Forwarded-Proto: https` を見て処理します。訪問者の IP は `CF-Connecting-IP` から戻します。
- **セキュリティ設定**: `DISALLOW_FILE_EDIT`、コアの自動更新を無効、`xmlrpc.php` をブロック、`DISABLE_WP_CRON`。

### 4.2 確認の状況

| 確認 | 結果 |
|---|---|
| Windows の Docker で一時的な環境を作り、手順をリハーサル | 取り込み、uploads の復元、アドレスの置換、プラグインのバージョン固定インストール、主要ページの応答、`xmlrpc.php` が 403、バックアップと復元 |
| M1 実機への移行(2026-10-26) | 取り込み、アドレスの置換、プラグインのインストール、主要ページが 200、混在コンテンツなし |
| 公開アドレス | 主要ページが 200、ヒーロー動画も正常(2026-10-26) |
| 確認していないこと | メール送信(SMTP)、公開アドレスでのカード決済、Lighthouse |

### 4.3 移行の手順

#### ① サーバーの準備(Mac)

```bash
mkdir -p ~/server/apps ~/server/backups/kapla-store/import
cd ~/server/apps
git clone https://github.com/ottuck/kapla-store.git
cd kapla-store/infra/home-server
cp .env.example .env && chmod 600 .env
```

`.env` のパスワードと salt は次のように作ります。

```bash
openssl rand -base64 24 | tr -d '/+='    # KAPLA_DB_PASSWORD と KAPLA_DB_ROOT_PASSWORD にそれぞれ 1 回ずつ
for k in AUTH_KEY SECURE_AUTH_KEY LOGGED_IN_KEY NONCE_KEY AUTH_SALT SECURE_AUTH_SALT LOGGED_IN_SALT NONCE_SALT; do echo "WORDPRESS_$k=$(openssl rand -base64 48 | tr -d '\n')"; done
```

以降のコマンドはすべて `infra/home-server` で実行します。

```bash
alias kc='docker compose -f compose.home.yaml'
alias wpc='docker compose -f compose.home.yaml --profile tools run --rm -T wpcli wp'
```

#### ② ローカルから書き出す(Windows)

Local のサイトを Start した状態で、PowerShell で実行します。`wp db export` は Local の環境では `mysqldump` を見つけられないため、Local に同梱されている `mysqldump` を直接使います(ポートとアカウントは Local の初期値)。

```powershell
$out = "$HOME\Desktop\kapla-export"; New-Item -ItemType Directory -Force $out | Out-Null
$d = "$env:APPDATA\Local\lightning-services\mysql-8.4.0+2\bin\win64\bin"
& "$d\mysqldump.exe" --host=127.0.0.1 --port=10005 --user=root --password=root --single-transaction --no-tablespaces --default-character-set=utf8mb4 "--result-file=$out\kapla.sql" local
tar -czf "$out\uploads.tgz" -C "$HOME\Local Sites\kapla\app\public\wp-content\uploads" .
scp "$out\kapla.sql" "$out\uploads.tgz" <mac-user>@<mac-host>:~/server/backups/kapla-store/import/
```

ダンプには管理者アカウントのハッシュ、Stripe のテストキー、お客様の情報が含まれます。送ったあとは `$out` を削除します。

#### ③ 起動と取り込み(Mac)

```bash
kc up -d db wordpress          # 初回は WordPress のファイルがボリュームにコピーされるまで数十秒かかります
kc ps                          # db: healthy、wordpress: running

# データベースの取り込み。wpcli(mariadb クライアント)は MySQL 8.4 の TLS 設定と合わないため、db コンテナの mysql を使います。
kc exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < ~/server/backups/kapla-store/import/kapla.sql

# uploads の復元(www-data の権限で)
kc exec -T -u www-data wordpress mkdir -p /var/www/html/wp-content/uploads
kc exec -T -u www-data wordpress tar xzf - -C /var/www/html/wp-content/uploads < ~/server/backups/kapla-store/import/uploads.tgz

# アドレスの置換(先に --dry-run で件数を確認します)
wpc search-replace 'http://kapla.local' 'https://kapla.portfolio-hub.dev' --all-tables --skip-columns=guid --dry-run
wpc search-replace 'http://kapla.local' 'https://kapla.portfolio-hub.dev' --all-tables --skip-columns=guid
wpc search-replace 'kapla.local' 'kapla.portfolio-hub.dev' --all-tables --skip-columns=guid   # お問い合わせフォームの送信元など、残ったもの

# プラグインのインストール(Git に入っていないため、ローカルと同じバージョンに固定。有効化の状態はデータベースに入っています)
wpc plugin install woocommerce --version=11.2.1
wpc plugin install woocommerce-gateway-stripe --version=11.1.1
wpc plugin install contact-form-7 --version=6.2.3
wpc plugin install seo-simple-pack --version=3.7.0
wpc rewrite flush && wpc transient delete --all && wpc cache flush
```

`curl -I http://127.0.0.1:8081/` が 200、または公開アドレスへのリダイレクトなら問題ありません。

#### ④ Cloudflare Tunnel の接続

`~/.cloudflared/config.yml` の `ingress` で、最後の 404 ルールの上に次を追加し、cloudflared を再起動します。

```yaml
  - hostname: kapla.portfolio-hub.dev
    service: http://localhost:8081
```

```bash
cloudflared tunnel ingress validate
```

DNS は Cloudflare のダッシュボードで `kapla` のレコードを追加します(タイプ CNAME、ターゲット `<トンネル ID>.cfargotunnel.com`、プロキシ有効)。1 つの Tunnel を複数のドメインで使っている場合、`cloudflared tunnel route dns` は別のゾーンにレコードを作ることがあるため、ダッシュボードで直接追加するほうが安全です。

```bash
curl -I https://kapla.portfolio-hub.dev/
```

### 4.4 運用のコマンド

```bash
kc ps                     # 状態
docker stats --no-stream  # メモリ(目安: wordpress と db の合計で約 1GB 以下)
kc logs -f wordpress      # ログ(json-file 10MB × 3 に制限)
git pull && kc up -d      # テーマ・プラグインのコードを反映(パターンを変えたときは wpc transient delete --all)
kc pull && kc up -d       # イメージの更新(compose.home.yaml のタグを変えたあと。コアのバージョンが変わったら wpc core update-db)
kc down                   # 停止(ボリュームは残る)
```

**`kc down -v` は使いません。** `-v` を付けるとデータベースと uploads のボリュームが消えます。

### 4.5 公開時の注意

- 管理者アカウントはダンプと一緒に移ります。実際の運用に切り替えるときは、`admin` 以外の名前の管理者アカウントと長いパスワードを使います。
- テーマのパターンを変更したあとはキャッシュを消します(`wpc transient delete --all`)。

### 4.6 必要になったときの参考(いまは行わない)

<details>
<summary>Cloudflare のキャッシュ除外・管理画面の保護・Stripe Webhook</summary>

**キャッシュ除外(Cache Rules)**: 次のどれかに当てはまれば `Bypass cache`。静的ファイル(テーマ、uploads、動画)は通常どおりキャッシュします。

```
(http.request.uri.path contains "/cart") or (http.request.uri.path contains "/checkout") or
(http.request.uri.path contains "/my-account") or (http.request.uri.path contains "/wp-admin") or
(http.request.uri.path eq "/wp-login.php") or (http.request.uri.path contains "/wp-json") or
(http.cookie contains "wordpress_logged_in") or (http.cookie contains "woocommerce_") or
(http.cookie contains "wp_woocommerce_session")
```

**管理画面の保護(Zero Trust → Access)**: `/wp-admin` と `/wp-login.php` を自分のメールアドレスだけに許可します。`/wp-admin/admin-ajax.php` は訪問者の画面(カートなど)でも使うため、Bypass ポリシーで除外します。

**Stripe Webhook**: Stripe のダッシュボードに `https://kapla.portfolio-hub.dev/?wc-api=wc_stripe` を登録し、署名シークレットを WooCommerce → 設定 → 決済 → Stripe に入力します。

</details>

<details>
<summary>WP-Cron とバックアップ</summary>

`DISABLE_WP_CRON` を有効にしているため、Mac の crontab から 5 分ごとに呼び出します(curl だけなのでメモリは使いません)。

```bash
*/5 * * * * curl -fsS -o /dev/null "http://127.0.0.1:8081/wp-cron.php?doing_wp_cron"
```

毎日データベースと uploads を保存し、14 日分だけ残すスクリプトの例:

```bash
#!/bin/bash
set -euo pipefail
cd ~/server/apps/kapla-store/infra/home-server
D=~/server/backups/kapla-store; T=$(date +%Y%m%d-%H%M)
docker compose -f compose.home.yaml exec -T db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --no-tablespaces "$MYSQL_DATABASE"' | gzip > "$D/db-$T.sql.gz"
docker compose -f compose.home.yaml exec -T wordpress tar czf - -C /var/www/html/wp-content/uploads . > "$D/uploads-$T.tgz"
find "$D" -maxdepth 1 -type f \( -name 'db-*' -o -name 'uploads-*' \) -mtime +14 -delete
```

復元のリハーサルは、別のプロジェクト名(`-p kapla-restore`)で空の環境を立ち上げてダンプを入れてみたあと、`down -v` でその環境だけを消します。

</details>

<details>
<summary>メール送信(SMTP)</summary>

コンテナにはメールを送る仕組みがありません。お問い合わせ・注文のメールを送るには SMTP(プラグインまたは外部サービス)を設定し、お問い合わせフォームの送信元アドレスを、SMTP で認証したドメインのアドレスに変えます。

</details>

## 5. 納品用ホスティングの事前調査

> 調査日 2026-10-25、各社の公式ページによる(税込、キャンペーン価格を含む)。**契約はしていません。** お客様に納品するときの互換性の判断と提案に使う参考資料で、いまの公開環境は自宅サーバーです。

| 項目 | XServer スタンダード | ConoHa WING ベーシック |
|---|---|---|
| 月額 | 990円〜(キャンペーン時 693円〜) | 12 か月 970円、36 か月 659円 など期間別。時間課金(1 時間 2.5円、月上限 1,452円)もあり |
| 初期費用 | 0円 | 0円 |
| 契約 | 期間ごと | WING パックは前払いで、途中解約不可 |
| 無料お試し | 10 日間 | 記載なし |
| CPU / メモリ | 6 コア / 8GB(新世代サーバー、リソース保証) | 6vCPU / 8GB(目標値) |
| ディスク | 500GB NVMe SSD | 500GB SSD |
| PHP | 8.5 / 8.4 / 8.3 推奨 | 7.0〜8.5 |
| SSH | 確認できず | 利用可 |
| バックアップ | 記載なし | 毎日自動、14 日分 |
| SSL | 無料 | 無料 |
| 注意 | 上位プランのプレミアムは 1,980円〜 | 調査時点で新規申し込みの一時停止の表記あり |

- **確認できなかったこと**: 両社とも PHP の `memory_limit`・`max_execution_time`、WP-CLI への対応、WooCommerce の動作保証。契約前に無料お試しで、管理画面の「ツール → サイトヘルス → 情報」から確認します。
- **納品するとき**: PHP 8.5.12 で作っているため、納品先のホスティングの PHP バージョンで一度互換性を確認します。SSH・WP-CLI が使える保証はないので、引き継ぎ資料は管理画面だけで操作できるように書きます。Docker の構成は自宅サーバー用のため、納品物には含めません。
- **対象外にしたところ**: ロリポップ!、さくらのレンタルサーバ、mixhost、CORE SERVER(情報不足または予算の理由)。市場シェアは信頼できる公的な統計がないため、提案書には書きません。

出典: [XServer](https://www.xserver.ne.jp/) ・ [XServer サーバー仕様一覧](https://www.xserver.ne.jp/manual/man_server_spec.php) ・ [XServer PHPのバージョン](https://www.xserver.ne.jp/manual/man_program_php_ver.php) ・ [ConoHa WING](https://www.conoha.jp/wing/) ・ [ConoHa WING リソース](https://www.conoha.jp/wing/function/resource/) ・ [ConoHa WING PHPの設定](https://support.conoha.jp/w/configphp/)
