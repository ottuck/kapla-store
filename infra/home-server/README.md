# Kapla Store 홈서버 배포 (M1 + Docker + Cloudflare Tunnel)

로컬(Local)에서 만든 사이트를 M1 맥북 홈서버의 Docker Compose로 옮겨 `https://kapla.portfolio-hub.dev`로 공개하기 위한 파일과 절차입니다.

```
Internet → Cloudflare → Tunnel(<tunnel-name>) → 127.0.0.1:8081 → wordpress(Apache, PHP 8.5) → db(MySQL 8.4)
```

| 파일 | 설명 |
|---|---|
| `compose.home.yaml` | `wordpress`, `db`, `wpcli`(도구 프로파일) 정의. PHP/Apache 설정은 파일 안의 `configs`에 들어 있다 |
| `.env.example` | 변수 목록. `.env`로 복사해 채운다(`.env`는 Git에 올라가지 않는다) |

설계 요점

- 이미지는 로컬과 같은 `wordpress:7.1.3-php8.5-apache`(PHP 8.5.12)다. WordPress는 MySQL/MariaDB만 지원하므로 DB는 `mysql:8.4`를 따로 둔다. arm64 이미지가 있다.
- 포트는 `127.0.0.1:8081`에만 열고 DB는 포트를 공개하지 않는다. 공개는 Tunnel로만 한다.
- 볼륨은 `kapla-wp`(WordPress 코어, 플러그인, uploads = `/var/www/html`)와 `kapla-db`(DB) 두 개다. 테마(`themes/kapla`)와 플러그인(`plugins/kapla-shop-tweaks`)은 Git 저장소를 읽기 전용으로 겹쳐 마운트하므로 **코드는 `git pull`로만 바꾼다**.
- 메모리 상한은 `wordpress` 512m, `db` 512m, `wpcli` 256m이다. Apache 동시 처리(MaxRequestWorkers)는 6으로 줄였다.
- 터널 뒤 HTTPS 인식은 공식 이미지의 wp-config가 `X-Forwarded-Proto: https`를 보고 처리한다. 방문자 IP는 `CF-Connecting-IP`로 되돌린다(포트가 루프백에만 열려 있어 터널을 거친 요청만 들어온다).
- `wp-config.php`의 `DISALLOW_FILE_EDIT`, `DISABLE_WP_CRON`(시스템 cron으로 대체), 코어 자동 업데이트 끔, `xmlrpc.php` 차단이 적용되어 있다.
- 이 문서의 3~7장 명령은 Windows의 Docker(amd64)에서 임시 스택(포트 18081)으로 실행해 확인했다: 로컬 덤프 가져오기, uploads 복원, 주소 치환(44건 + 15건), 플러그인 4종 고정 설치와 활성 상태 유지, 메인·브랜드·FAQ·문의·카트 페이지 응답(`Host`/`X-Forwarded-Proto` 헤더로 터널 뒤 동작을 흉내), `xmlrpc.php` 403, wp-cron 200, 백업과 복원 리허설. 유휴 메모리는 wordpress 약 135MB, db 약 223MB였다. 같은 3장 절차를 M1 홈서버(arm64)에서도 실행해 확인했다(2026-10-26): 가져오기, 주소 치환(41건 + 3건), 플러그인 설치, 모든 주요 페이지 200, 혼합 콘텐츠(http 링크) 없음, 메모리는 wordpress 약 119MB, db 약 233MB. **공개 주소(DNS와 Cloudflare 설정, 4~5장), SMTP는 아직 확인하지 않았다.**
- 이 환경에서는 SSH 세션에서 macOS 키체인이 잠겨 있어 `gh`/`git`의 저장된 GitHub 토큰을 쓸 수 없다. PC에서 `git push <mac-user>@<mac>:server/apps/kapla-store main`으로 직접 보냈다(Mac 쪽은 `receive.denyCurrentBranch=updateInstead`).

## 0. 이전 전 확인

- [ ] Local에서 테스트 주문(#37, #38 등)과 테스트 고객 계정을 지운다(`docs/payments.md` 참고).
- [ ] 홈서버 공통 확인 항목(`cloudflared` 자동 시작, Docker 로그인 시 실행, 절전 방지, 재부팅 후 복구)을 확인한다.
- [ ] Tunnel `<tunnel-name>`이 **Mac에서** 실행되는지 확인한다(`cloudflared tunnel list`, `ps aux | grep cloudflared`).
- [ ] `portfolio-hub.dev`의 DNS가 Cloudflare에 있는지 확인한다.

## 1. 서버 준비 (Mac)

```bash
mkdir -p ~/server/apps ~/server/backups/kapla-store/import
cd ~/server/apps
git clone git@github.com:ottuck/kapla-store.git      # private 저장소: SSH 키 또는 gh auth 필요
cd kapla-store/infra/home-server
cp .env.example .env && chmod 600 .env
```

`.env`를 열어 값을 채운다. 비밀번호와 salt는 아래처럼 만든다.

```bash
openssl rand -base64 24 | tr -d '/+='    # KAPLA_DB_PASSWORD, KAPLA_DB_ROOT_PASSWORD 에 각각 한 번씩
for k in AUTH_KEY SECURE_AUTH_KEY LOGGED_IN_KEY NONCE_KEY AUTH_SALT SECURE_AUTH_SALT LOGGED_IN_SALT NONCE_SALT; do echo "WORDPRESS_$k=$(openssl rand -base64 48 | tr -d '\n')"; done
```

이후 명령은 모두 `~/server/apps/kapla-store/infra/home-server`에서 실행한다. 편의상:

```bash
alias kc='docker compose -f compose.home.yaml'
```

## 2. 로컬에서 내보내기 (Windows)

Local 사이트를 Start 한 상태에서 PowerShell로 실행한다. `wp db export`는 Local 환경에서 `mysqldump`를 못 찾으므로 Local이 번들한 `mysqldump`를 직접 쓴다(포트 10005, 계정 root/root는 Local 기본값).

```powershell
$out = "$HOME\Desktop\kapla-export"; New-Item -ItemType Directory -Force $out | Out-Null
$d = "$env:APPDATA\Local\lightning-services\mysql-8.4.0+2\bin\win64\bin"
& "$d\mysqldump.exe" --host=127.0.0.1 --port=10005 --user=root --password=root --single-transaction --no-tablespaces --default-character-set=utf8mb4 "--result-file=$out\kapla.sql" local
tar -czf "$out\uploads.tgz" -C "$HOME\Local Sites\kapla\app\public\wp-content\uploads" .
scp "$out\kapla.sql" "$out\uploads.tgz" <mac-user>@<mac-tailscale-name>:~/server/backups/kapla-store/import/
```

덤프에는 관리자 해시, Stripe 테스트 키, 고객 정보가 들어 있다. 전송 후 `$out`을 지운다.

## 3. 띄우기와 가져오기 (Mac)

```bash
kc up -d db wordpress          # 처음 실행은 WordPress 파일이 볼륨에 복사될 때까지 수십 초 걸린다
kc ps                          # db: healthy, wordpress: running

# DB 가져오기. wpcli(mariadb 클라이언트)는 MySQL 8.4의 TLS 설정과 맞지 않아 db 컨테이너의 mysql을 쓴다.
kc exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < ~/server/backups/kapla-store/import/kapla.sql

# uploads 복원 (www-data 권한으로)
kc exec -T -u www-data wordpress mkdir -p /var/www/html/wp-content/uploads
kc exec -T -u www-data wordpress tar xzf - -C /var/www/html/wp-content/uploads < ~/server/backups/kapla-store/import/uploads.tgz
```

WP-CLI는 `kc --profile tools run --rm -T wpcli wp <명령>`으로 실행한다(명령 앞의 `wp`를 빼면 실행되지 않는다). 아래에서는 `wpc`로 줄여 쓴다.

```bash
alias wpc='docker compose -f compose.home.yaml --profile tools run --rm -T wpcli wp'

# 주소 치환 (먼저 --dry-run 으로 건수를 본다)
wpc search-replace 'http://kapla.local' 'https://kapla.portfolio-hub.dev' --all-tables --skip-columns=guid --dry-run
wpc search-replace 'http://kapla.local' 'https://kapla.portfolio-hub.dev' --all-tables --skip-columns=guid
wpc search-replace 'kapla.local' 'kapla.portfolio-hub.dev' --all-tables --skip-columns=guid   # 문의 폼 발신자 wordpress@kapla.local 등 남은 것

# 플러그인 설치 (Git에 없으므로 로컬과 같은 버전으로 고정한다. 활성화 상태는 DB에 들어 있다)
wpc plugin install woocommerce --version=11.2.1
wpc plugin install woocommerce-gateway-stripe --version=11.1.1
wpc plugin install contact-form-7 --version=6.2.3
wpc plugin install seo-simple-pack --version=3.7.0
wpc plugin list --fields=name,status,version
wpc theme list --fields=name,status
wpc rewrite flush
wpc transient delete --all
wpc cache flush
```

확인: `curl -I http://127.0.0.1:8081/`이 200(또는 정상 리다이렉트)인지 본다. 이 시점 `https://kapla.portfolio-hub.dev`로 리다이렉트되므로 브라우저 확인은 아래 Tunnel 연결 뒤에 한다.

## 4. Cloudflare Tunnel 연결

`~/.cloudflared/config.yml`의 `ingress`에 아래를 **마지막 404 규칙 위에** 추가한다.

```yaml
  - hostname: kapla.portfolio-hub.dev
    service: http://localhost:8081
```

```bash
cloudflared tunnel ingress validate
launchctl kickstart -k gui/$(id -u)/com.cloudflare.cloudflared   # cloudflared 재시작
```

**DNS는 `cloudflared tunnel route dns`를 쓰지 않는다.** 홈서버의 `~/.cloudflared/cert.pem`은 `<other-domain>` 존용이라, 다른 도메인(`portfolio-hub.dev`)에 쓰면 `kapla.portfolio-hub.dev.<other-domain>`라는 엉뚱한 레코드가 만들어진다. 대신 Cloudflare 대시보드 → `portfolio-hub.dev` → DNS → 레코드 추가로 직접 넣는다.

| 유형 | 이름 | 대상 | 프록시 |
|---|---|---|---|
| CNAME | `kapla` | `<tunnel-id>.cfargotunnel.com` | 켬(주황 구름) |

```bash
curl -I https://kapla.portfolio-hub.dev/
```

참고: 서버의 launchd 설정(`~/Library/LaunchAgents/com.cloudflare.cloudflared.plist`)은 `ProgramArguments`가 `cloudflared`뿐이라 사용법만 출력하고 종료를 반복했다. `tunnel`, `run` 인자를 추가해 고쳤다(2026-10-26, 원본은 `.plist.bak-20261026`).

> **범위 안내**: 이 프로젝트는 포트폴리오 데모라서 5~7장(캐시·Access, cron, 백업과 복원 리허설)과 9장의 SMTP·키 교체는 **하지 않기로 했다**(2026-10-26 사용자 결정). 외주 문의가 실제로 오가거나 상시 운영이 필요해질 때 참고한다.

## 5. Cloudflare 대시보드 설정

**캐시 우회(Cache Rules)**: 아래 조건 중 하나라도 맞으면 `Bypass cache`.

```
(http.request.uri.path contains "/cart") or (http.request.uri.path contains "/checkout") or
(http.request.uri.path contains "/my-account") or (http.request.uri.path contains "/wp-admin") or
(http.request.uri.path eq "/wp-login.php") or (http.request.uri.path contains "/wp-json") or
(http.cookie contains "wordpress_logged_in") or (http.cookie contains "woocommerce_") or
(http.cookie contains "wp_woocommerce_session")
```

정적 파일(테마, uploads)은 기본 캐시를 쓴다. 영상(`hero.mp4`, 약 1.4MB)도 캐시된다.

**관리자 보호(Zero Trust → Access)**: `kapla.portfolio-hub.dev/wp-admin`과 `/wp-login.php`를 본인 이메일만 허용하는 Access 앱으로 보호한다. 단 `/wp-admin/admin-ajax.php`는 방문자 화면(장바구니 등)도 쓰므로 **Bypass 정책으로 예외**를 둔다.

**Stripe 웹훅**: 공개 주소가 동작한 뒤 Stripe 샌드박스 대시보드에서 `https://kapla.portfolio-hub.dev/?wc-api=wc_stripe`를 엔드포인트로 등록하고, 서명 시크릿을 WooCommerce → 설정 → 결제 → Stripe 화면에 넣는다. 이 경로는 Access 대상이 아니다.

## 6. WP-Cron (Mac의 crontab)

`DISABLE_WP_CRON`을 켜 두었으므로 5분마다 호출한다. 컨테이너를 띄우지 않고 curl만 쓰므로 메모리를 쓰지 않는다.

```bash
crontab -e
*/5 * * * * curl -fsS -o /dev/null "http://127.0.0.1:8081/wp-cron.php?doing_wp_cron"
```

## 7. 백업

`~/server/backups/kapla-store/`에 매일 DB 덤프와 uploads를 저장하고 14일치만 남기는 예다. `~/server/infra/scripts/backup-kapla.sh`로 저장하고 `chmod +x` 한다.

```bash
#!/bin/bash
set -euo pipefail
cd ~/server/apps/kapla-store/infra/home-server
D=~/server/backups/kapla-store; T=$(date +%Y%m%d-%H%M)
export PATH="/usr/local/bin:/opt/homebrew/bin:$PATH"
docker compose -f compose.home.yaml exec -T db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --no-tablespaces "$MYSQL_DATABASE"' | gzip > "$D/db-$T.sql.gz"
docker compose -f compose.home.yaml exec -T wordpress tar czf - -C /var/www/html/wp-content/uploads . > "$D/uploads-$T.tgz"
find "$D" -maxdepth 1 -type f \( -name 'db-*' -o -name 'uploads-*' \) -mtime +14 -delete
```

```bash
crontab -e
30 3 * * * ~/server/infra/scripts/backup-kapla.sh >> ~/server/backups/kapla-store/backup.log 2>&1
```

**복원 리허설 (1회 필수)**: 임시 프로젝트 이름으로 빈 스택을 띄워 덤프를 넣어 본다.

```bash
docker compose -p kapla-restore -f compose.home.yaml up -d db
gunzip -c ~/server/backups/kapla-store/db-<날짜>.sql.gz | docker compose -p kapla-restore -f compose.home.yaml exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'
docker compose -p kapla-restore -f compose.home.yaml exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" -e "select count(*) from wp_posts"'
docker compose -p kapla-restore -f compose.home.yaml down -v     # 리허설 스택만 삭제(-p 로 이름이 달라 운영 볼륨은 영향 없음)
```

백업은 같은 Mac 안에 있으므로 가끔 Windows로 복사해 두는 것을 권한다(`scp`).

## 8. 운영 명령

```bash
kc ps                     # 상태
docker stats --no-stream  # 메모리 확인 (목표: wordpress+db 합쳐 약 1GB 이하)
kc logs -f wordpress      # 로그 (json-file 10MB × 3 으로 제한됨)
git pull && kc up -d      # 테마/플러그인 코드 변경 반영 (패턴을 고쳤다면 wpc transient delete --all)
kc pull && kc up -d       # 이미지 갱신 (compose.home.yaml 의 태그를 바꾼 뒤. 코어 버전이 바뀌면 wpc core update-db)
kc down                   # 중지(볼륨 유지)
```

**`kc down -v`는 쓰지 않는다.** `-v`는 DB와 uploads 볼륨을 지운다.

## 9. 남은 작업과 주의

- 주문·문의 메일: 컨테이너에는 메일 발송 기능이 없다. SMTP 릴레이 설정이 필요하고(별도 작업), 문의 폼(Contact Form 7)의 발신자 주소는 SMTP에서 인증된 도메인 주소로 바꿔야 한다.
- 관리자 비밀번호: 덤프에 로컬 관리자 계정이 들어 있다. 로그인 후 강한 비밀번호로 바꾼다.
- 로컬 Stripe 테스트 키를 채팅에 붙인 적이 있으므로, 프로젝트를 마치면 Stripe 대시보드에서 키를 교체(Rotate)하고 DB에 새 키를 넣는다.
- 공개 후 Lighthouse를 측정해 `docs/quality-check.md`에 기록하고, 모바일 화면과 결제(테스트 카드)를 공개 주소에서 다시 확인한다.
