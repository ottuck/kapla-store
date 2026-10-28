# 프로젝트 기록 — 설정·공개·운영

> 갱신: 2026-10-28 · 결제·배송, 문의 폼과 SEO, 공개 환경(자택 서버), 납품용 호스팅 조사를 한곳에 모은 문서다. 디자인은 [design-system.md](design-system.md), 상품 사진은 [ai-image-prompts.md](ai-image-prompts.md)를 본다.

## 목차

1. [현재 상태 한눈에 보기](#1-현재-상태-한눈에-보기)
2. [결제·배송](#2-결제배송)
3. [문의 폼과 SEO](#3-문의-폼과-seo)
4. [공개 환경: 자택 서버](#4-공개-환경-자택-서버)
5. [납품용 호스팅 사전 조사](#5-납품용-호스팅-사전-조사)

## 1. 현재 상태 한눈에 보기

| 항목 | 상태 |
|---|---|
| 공개 주소 | https://kapla.portfolio-hub.dev (자택 서버라 꺼져 있는 날이 있다) |
| 버전 | WordPress 7.1.3, WooCommerce 11.2.1, PHP 8.5.12, MySQL 8.4 |
| 통화·세금 | JPY(소수점 0), 税込 입력·표시, 消費税 10% |
| 결제 | Stripe 테스트 모드(카드), 代金引換. 실제 결제는 하지 않는다 |
| 배송 | 일본만. 전국 500엔, 8,000엔(税込) 이상 무료 |
| 검색 노출 | 데모 기간에는 `noindex, nofollow` |

**범위는 포트폴리오 데모 수준이다.** SMTP 메일 릴레이, 백업과 복원 리허설, 정기 cron, Cloudflare 캐시 규칙·Access 보호, Stripe 웹훅은 하지 않기로 했다. 실제 운영이 필요해질 때 [4.6](#46-필요해질-때-참고-지금은-하지-않음)을 참고한다.

## 2. 결제·배송

### 2.1 설정

| 항목 | 내용 |
|---|---|
| 배송 지역 | `日本` 존 하나 |
| 송료 | 전국 일률 500엔(데모에서는 비과세로 취급해 정확히 500엔 표시). 8,000엔(税込) 이상 무료이고, 무료 조건을 만족하면 유료 송료는 선택지에서 숨긴다 |
| 代金引換 | 사용. Stripe 없이도 주문 흐름을 확인할 수 있다 |
| Stripe | `woocommerce-gateway-stripe` 11.1.1, 테스트 키(샌드박스, JPY) |
| 체크아웃 | 블록 방식. 일본 주소 입력란(姓·名, 郵便番号, 都道府県, 市区町村, 住所) |

일본에 맞춘 조정:

- 결제 수단 이름 `Credit / Debit Card` → `クレジットカード / デビットカード` (`plugins/kapla-shop-tweaks`의 gettext 필터)
- Stripe Link, Apple Pay, Google Pay, 간편 결제 버튼(express checkout)은 끔. Link 등 결제 수단 설정은 Stripe 계정 쪽에 저장되므로 Git에는 남지 않는다
- 저장된 카드(`saved_cards`)를 꺼서 카드 저장 안내 문구를 없앰
- 샌드박스 기본 설정에 켜져 있는 해외 결제 수단(Bancontact 등)은 JPY·일본 주문에서는 나타나지 않는 것을 확인함(카드만 표시)

송료·결제 설정은 `scripts/seed-checkout.php`로 다시 만들 수 있다. 무료 송료일 때 유료 송료를 숨기는 처리는 `plugins/kapla-shop-tweaks`에 있다(테마에는 주문 로직을 넣지 않는다).

### 2.2 확인한 주문 흐름 (로컬)

| 결제 | 주문 내용 | 결과 |
|---|---|---|
| 代金引換 | SALAR オイルパフューム ¥5,500 | 합계 ¥6,000(송료 ¥500, 소비세 ¥500 포함), 상태 `processing`, 완료 화면 일본어 표시 |
| 代金引換 | ギフトセット ¥8,800 | 무료 송료만 표시, 합계 ¥8,800 |
| Stripe 테스트 카드 | ギフトセット + DUSK オイルパフューム | 송료 무료, 합계 ¥14,300(소비세 ¥1,300 포함), 결제 완료, `livemode=false` |

- 로컬(HTTP, 웹훅 없음)에서도 카드 결제와 주문 확정까지 동작했다.
- 테스트 주문과 테스트 고객 기록은 공개 전에 지웠다(2026-10-26).

### 2.3 Stripe 테스트 키 넣기

비밀 키는 채팅이나 Git에 남기지 않는다. 키는 DB(`woocommerce_stripe_settings`)에만 저장된다.

1. [Stripe](https://dashboard.stripe.com/register)에서 계정을 만들고 **테스트 모드**로 전환한다.
2. 開発者 → API キー에서 `pk_test_…`와 `sk_test_…`를 확인한다.
3. 아래를 실행해 키를 넣는다. 입력은 화면에 표시되지 않고, `pk_test_` / `sk_test_`만 받는다.

```powershell
.\scripts\stripe-test-keys.ps1
```

4. 결제 화면에서 테스트 카드 `4242 4242 4242 4242`(만료일은 미래 아무 날짜, CVC 아무 숫자)로 주문한다.

## 3. 문의 폼과 SEO

### 3.1 플러그인

| 플러그인 | 버전 | 비고 |
|---|---|---|
| Contact Form 7 | 6.2.3 | 일본어 번역 설치(`wp language plugin install contact-form-7 ja`) |
| SEO SIMPLE PACK | 3.7.0 | PHP 8.5.12에서 오류 없음 |

### 3.2 문의 폼 (`scripts/seed-contact.php`)

- 제목 `お問い合わせ`. 항목: お名前(필수), メールアドレス(필수), お問い合わせの種類(선택), お問い合わせ内容(필수), 개인정보 동의(필수), 送信する.
- 문의 페이지(`/contact/`)에 블록으로 넣었다. 입력란 스타일은 `style.css`의 `.wpcf7` 규칙.
- 메일은 관리자 수신과 보낸 사람 자동 회신 2통.
- 스팸 방지: 사람에게 보이지 않는 입력란 `kp_website`(honeypot)가 채워지면 스팸으로 처리한다(`plugins/kapla-shop-tweaks`).

로컬에서 확인한 것:

- 동의를 체크하고 보내면 완료 문구가 표시되고, Local의 Mailpit에 관리자 메일과 자동 회신이 도착했다. 주문 메일(관리자·고객)도 도착했다.
- honeypot 입력란을 채운 요청은 `spam`으로 막혔고 메일이 가지 않았다.
- 동의 체크박스는 사람이 실제로 눌러야 보내기 버튼이 켜진다.

> 공개 환경에는 메일 발송 수단이 없어 **문의·주문 메일은 보내지지 않는다**(SMTP 릴레이는 범위 밖).

### 3.3 SEO (`scripts/seed-seo.php`)

- 메인 페이지에 `<title>`, `description`, canonical, OGP(`og:*`), `twitter:card`가 출력된다. 설명문은 일본어.
- WordPress 기본 사이트맵 `/wp-sitemap.xml`.
- 데모 기간에는 `noindex, nofollow`(`blog_public = 0`). 검색에 노출하려면 設定 → 表示設定의 「検索エンジンがサイトをインデックスしないようにする」를 끈다.

## 4. 공개 환경: 자택 서버

로컬(LocalWP)에서 만든 사이트를 M1 MacBook Air의 Docker Compose로 옮겨 Cloudflare Tunnel로 공개했다. 배치 파일은 `infra/home-server/`에 있다.

```
방문자 → Cloudflare(DNS·HTTPS) → Tunnel → 127.0.0.1:8081 → wordpress(Apache, PHP 8.5) → db(MySQL 8.4)
```

| 파일 | 설명 |
|---|---|
| `infra/home-server/compose.home.yaml` | `wordpress`, `db`, `wpcli`(tools 프로파일). PHP·Apache 설정은 파일 안의 `configs`에 있다 |
| `infra/home-server/.env.example` | 변수 목록. `.env`로 복사해 채운다(`.env`는 Git에 올리지 않는다) |

### 4.1 설계 요점

- **이미지**: 로컬과 같은 `wordpress:7.1.3-php8.5-apache`와 `mysql:8.4`(둘 다 arm64 있음).
- **포트**: `127.0.0.1:8081`에만 열고 DB는 포트를 열지 않는다. 외부 공개는 Tunnel로만 하고 공유기 포트는 열지 않는다.
- **볼륨**: `kapla-wp`(코어·플러그인·uploads)와 `kapla-db` 두 개. 테마와 `kapla-shop-tweaks`는 Git 저장소를 읽기 전용으로 겹쳐 마운트하므로 **코드는 Git으로만 바꾼다**.
- **메모리 상한**: wordpress 512m, db 512m, wpcli 256m. Apache 동시 처리(MaxRequestWorkers)는 6. 실제 유휴 메모리는 wordpress 약 120MB, db 약 230MB.
- **HTTPS**: 공식 이미지의 wp-config가 `X-Forwarded-Proto: https`를 보고 처리한다. 방문자 IP는 `CF-Connecting-IP`로 되돌린다.
- **보안 설정**: `DISALLOW_FILE_EDIT`, 코어 자동 업데이트 끔, `xmlrpc.php` 차단, `DISABLE_WP_CRON`.

### 4.2 검증 상태

| 확인 | 결과 |
|---|---|
| Windows Docker에서 임시 스택으로 절차 리허설 | 가져오기, uploads 복원, 주소 치환, 플러그인 고정 설치, 주요 페이지 응답, `xmlrpc.php` 403, 백업·복원 |
| M1 실기 이전(2026-10-26) | 가져오기, 주소 치환, 플러그인 설치, 주요 페이지 200, 혼합 콘텐츠 없음 |
| 공개 주소 | 주요 페이지 200, 히어로 영상 정상(2026-10-26) |
| 확인하지 않은 것 | 메일 발송(SMTP), 공개 주소에서의 카드 결제, Lighthouse |

### 4.3 이전 절차

#### ① 서버 준비 (Mac)

```bash
mkdir -p ~/server/apps ~/server/backups/kapla-store/import
cd ~/server/apps
git clone https://github.com/ottuck/kapla-store.git
cd kapla-store/infra/home-server
cp .env.example .env && chmod 600 .env
```

`.env`의 비밀번호와 salt는 아래처럼 만든다.

```bash
openssl rand -base64 24 | tr -d '/+='    # KAPLA_DB_PASSWORD, KAPLA_DB_ROOT_PASSWORD에 각각 한 번씩
for k in AUTH_KEY SECURE_AUTH_KEY LOGGED_IN_KEY NONCE_KEY AUTH_SALT SECURE_AUTH_SALT LOGGED_IN_SALT NONCE_SALT; do echo "WORDPRESS_$k=$(openssl rand -base64 48 | tr -d '\n')"; done
```

이후 명령은 모두 `infra/home-server`에서 실행한다.

```bash
alias kc='docker compose -f compose.home.yaml'
alias wpc='docker compose -f compose.home.yaml --profile tools run --rm -T wpcli wp'
```

#### ② 로컬에서 내보내기 (Windows)

Local 사이트를 Start 한 상태에서 PowerShell로 실행한다. `wp db export`는 Local에서 `mysqldump`를 못 찾으므로 Local에 들어 있는 `mysqldump`를 직접 쓴다(포트와 계정은 Local 기본값).

```powershell
$out = "$HOME\Desktop\kapla-export"; New-Item -ItemType Directory -Force $out | Out-Null
$d = "$env:APPDATA\Local\lightning-services\mysql-8.4.0+2\bin\win64\bin"
& "$d\mysqldump.exe" --host=127.0.0.1 --port=10005 --user=root --password=root --single-transaction --no-tablespaces --default-character-set=utf8mb4 "--result-file=$out\kapla.sql" local
tar -czf "$out\uploads.tgz" -C "$HOME\Local Sites\kapla\app\public\wp-content\uploads" .
scp "$out\kapla.sql" "$out\uploads.tgz" <mac-user>@<mac-host>:~/server/backups/kapla-store/import/
```

덤프에는 관리자 계정 해시, Stripe 테스트 키, 고객 정보가 들어 있다. 보낸 뒤 `$out`을 지운다.

#### ③ 띄우기와 가져오기 (Mac)

```bash
kc up -d db wordpress          # 첫 실행은 WordPress 파일이 볼륨에 복사될 때까지 수십 초 걸린다
kc ps                          # db: healthy, wordpress: running

# DB 가져오기. wpcli(mariadb 클라이언트)는 MySQL 8.4의 TLS 설정과 맞지 않아 db 컨테이너의 mysql을 쓴다.
kc exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < ~/server/backups/kapla-store/import/kapla.sql

# uploads 복원(www-data 권한)
kc exec -T -u www-data wordpress mkdir -p /var/www/html/wp-content/uploads
kc exec -T -u www-data wordpress tar xzf - -C /var/www/html/wp-content/uploads < ~/server/backups/kapla-store/import/uploads.tgz

# 주소 치환(먼저 --dry-run으로 건수를 본다)
wpc search-replace 'http://kapla.local' 'https://kapla.portfolio-hub.dev' --all-tables --skip-columns=guid --dry-run
wpc search-replace 'http://kapla.local' 'https://kapla.portfolio-hub.dev' --all-tables --skip-columns=guid
wpc search-replace 'kapla.local' 'kapla.portfolio-hub.dev' --all-tables --skip-columns=guid   # 문의 폼 발신자 등 남은 것

# 플러그인 설치(Git에 없으므로 로컬과 같은 버전으로 고정. 활성 상태는 DB에 들어 있다)
wpc plugin install woocommerce --version=11.2.1
wpc plugin install woocommerce-gateway-stripe --version=11.1.1
wpc plugin install contact-form-7 --version=6.2.3
wpc plugin install seo-simple-pack --version=3.7.0
wpc rewrite flush && wpc transient delete --all && wpc cache flush
```

`curl -I http://127.0.0.1:8081/`이 200 또는 공개 주소로의 리다이렉트이면 된다.

#### ④ Cloudflare Tunnel 연결

`~/.cloudflared/config.yml`의 `ingress`에서 마지막 404 규칙 위에 추가하고 cloudflared를 다시 시작한다.

```yaml
  - hostname: kapla.portfolio-hub.dev
    service: http://localhost:8081
```

```bash
cloudflared tunnel ingress validate
```

DNS는 Cloudflare 대시보드에서 `kapla` 레코드를 추가한다(유형 CNAME, 대상 `<터널 ID>.cfargotunnel.com`, 프록시 켬). 하나의 Tunnel을 여러 도메인에 쓰는 경우 `cloudflared tunnel route dns`는 다른 존에 레코드를 만들 수 있으므로 대시보드에서 직접 넣는 편이 안전하다.

```bash
curl -I https://kapla.portfolio-hub.dev/
```

### 4.4 운영 명령

```bash
kc ps                     # 상태
docker stats --no-stream  # 메모리(목표: wordpress + db 합쳐 약 1GB 이하)
kc logs -f wordpress      # 로그(json-file 10MB × 3으로 제한)
git pull && kc up -d      # 테마·플러그인 코드 반영(패턴을 고쳤다면 wpc transient delete --all)
kc pull && kc up -d       # 이미지 갱신(compose.home.yaml의 태그를 바꾼 뒤. 코어 버전이 바뀌면 wpc core update-db)
kc down                   # 중지(볼륨 유지)
```

**`kc down -v`는 쓰지 않는다.** `-v`는 DB와 uploads 볼륨을 지운다.

### 4.5 공개할 때 주의

- 관리자 계정은 덤프와 함께 옮겨진다. 실제 운영으로 바꿀 때는 `admin`이 아닌 관리자 계정과 긴 비밀번호를 쓴다.
- 테마 패턴을 고친 뒤에는 캐시를 지운다(`wpc transient delete --all`).

### 4.6 필요해질 때 참고 (지금은 하지 않음)

<details>
<summary>Cloudflare 캐시 우회·관리자 보호·Stripe 웹훅</summary>

**캐시 우회(Cache Rules)**: 아래 중 하나라도 맞으면 `Bypass cache`. 정적 파일(테마, uploads, 영상)은 기본 캐시를 쓴다.

```
(http.request.uri.path contains "/cart") or (http.request.uri.path contains "/checkout") or
(http.request.uri.path contains "/my-account") or (http.request.uri.path contains "/wp-admin") or
(http.request.uri.path eq "/wp-login.php") or (http.request.uri.path contains "/wp-json") or
(http.cookie contains "wordpress_logged_in") or (http.cookie contains "woocommerce_") or
(http.cookie contains "wp_woocommerce_session")
```

**관리자 보호(Zero Trust → Access)**: `/wp-admin`과 `/wp-login.php`를 본인 이메일만 허용한다. `/wp-admin/admin-ajax.php`는 방문자 화면(장바구니 등)도 쓰므로 Bypass 정책으로 예외를 둔다.

**Stripe 웹훅**: Stripe 대시보드에 `https://kapla.portfolio-hub.dev/?wc-api=wc_stripe`를 등록하고, 서명 시크릿을 WooCommerce → 設定 → 決済 → Stripe에 넣는다.

</details>

<details>
<summary>WP-Cron과 백업</summary>

`DISABLE_WP_CRON`을 켜 두었으므로 Mac의 crontab에서 5분마다 부른다(curl만 쓰므로 메모리를 쓰지 않는다).

```bash
*/5 * * * * curl -fsS -o /dev/null "http://127.0.0.1:8081/wp-cron.php?doing_wp_cron"
```

매일 DB와 uploads를 저장하고 14일치만 남기는 스크립트 예:

```bash
#!/bin/bash
set -euo pipefail
cd ~/server/apps/kapla-store/infra/home-server
D=~/server/backups/kapla-store; T=$(date +%Y%m%d-%H%M)
docker compose -f compose.home.yaml exec -T db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --no-tablespaces "$MYSQL_DATABASE"' | gzip > "$D/db-$T.sql.gz"
docker compose -f compose.home.yaml exec -T wordpress tar czf - -C /var/www/html/wp-content/uploads . > "$D/uploads-$T.tgz"
find "$D" -maxdepth 1 -type f \( -name 'db-*' -o -name 'uploads-*' \) -mtime +14 -delete
```

복원 리허설은 다른 프로젝트 이름(`-p kapla-restore`)으로 빈 스택을 띄워 덤프를 넣어 본 뒤 `down -v`로 그 스택만 지운다.

</details>

<details>
<summary>메일 발송(SMTP)</summary>

컨테이너에는 메일 발송 기능이 없다. 문의·주문 메일을 보내려면 SMTP 릴레이(플러그인 또는 외부 서비스)를 설정하고, 문의 폼의 발신자 주소를 SMTP에서 인증된 도메인 주소로 바꾼다.

</details>

## 5. 납품용 호스팅 사전 조사

> 조사일 2026-10-25, 각 사 공식 페이지 기준(세금·캠페인 가격 포함). **계약하지 않았다.** 고객에게 납품할 때 호환성 판단과 제안에 쓰는 참고 자료이며, 지금의 공개 환경은 자택 서버다.

| 항목 | XServer スタンダード | ConoHa WING ベーシック |
|---|---|---|
| 월 요금 | 990円〜(캠페인 시 693円〜) | 12개월 970円, 36개월 659円 등 기간별. 종량제(시간당 2.5円, 월 상한 1,452円)도 있음 |
| 초기 비용 | 0円 | 0円 |
| 계약 | 기간별 | WING 팩은 선결제, 중도 해지 불가 |
| 무료 체험 | 10일 | 기재 없음 |
| CPU / 메모리 | 6코어 / 8GB(신세대 서버, 리소스 보장) | 6vCPU / 8GB(목표값) |
| 디스크 | 500GB NVMe SSD | 500GB SSD |
| PHP | 8.5 / 8.4 / 8.3 권장 | 7.0〜8.5 |
| SSH | 확인 못 함 | 가능 |
| 백업 | 기재 없음 | 매일 자동, 14일분 |
| SSL | 무료 | 무료 |
| 주의 | 상위 플랜 プレミアム 1,980円〜 | 조사 시점에 신규 신청 일시 중단 표기 |

- **확인하지 못한 것**: 두 곳 모두 PHP `memory_limit`·`max_execution_time`, WP-CLI 지원, WooCommerce 동작 보증. 계약 전에 무료 체험에서 관리자 화면 「ツール → サイトヘルス → 情報」로 확인한다.
- **납품할 때**: PHP 8.5.12로 만들었으므로 대상 호스팅의 PHP 버전으로 한 번 호환 테스트를 한다. SSH·WP-CLI는 보장되지 않으므로 인수인계 문서는 관리자 화면만으로 따라 할 수 있게 쓴다. Docker 구성은 자택 서버용이라 납품물에 넣지 않는다.
- **제외한 곳**: ロリポップ!, さくらのレンタルサーバ, mixhost, CORE SERVER(정보 부족 또는 예산). 시장 점유율은 신뢰할 공적 통계가 없어 제안서에 쓰지 않는다.

출처: [XServer](https://www.xserver.ne.jp/) · [XServer サーバー仕様一覧](https://www.xserver.ne.jp/manual/man_server_spec.php) · [XServer PHPのバージョン](https://www.xserver.ne.jp/manual/man_program_php_ver.php) · [ConoHa WING](https://www.conoha.jp/wing/) · [ConoHa WING リソース](https://www.conoha.jp/wing/function/resource/) · [ConoHa WING PHPの設定](https://support.conoha.jp/w/configphp/)
