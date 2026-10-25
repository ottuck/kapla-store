# お問い合わせフォームとSEO設定

> 갱신: 2026-10-26 · 로컬(`kapla.local`) 기준

## 플러그인

| 플러그인 | 버전 | 비고 |
|---|---|---|
| Contact Form 7 | 6.2.3 | WP 7.1, PHP 8.3 이상 요구. 일본어 번역 설치(`wp language plugin install contact-form-7 ja`) |
| SEO SIMPLE PACK | 3.7.0 | PHP 8.5.12에서 오류 없음 |

## 문의 폼 (`scripts/seed-contact.php`)

- 폼 ID는 `#40`, 제목 `お問い合わせ`. 항목: お名前(필수), メールアドレス(필수), お問い合わせの種類(선택 목록), お問い合わせ内容(필수), 개인정보 동의(필수), 送信する.
- 문의 페이지(`/contact/`)에 블록으로 설치. 폼 입력란 스타일은 `style.css`의 `.wpcf7` 규칙.
- 메일은 2통: 관리자 수신(`[_site_admin_email]`) + 보낸 사람 자동 회신. 보낸 사람 주소는 `wordpress@kapla.local`이므로 **홈서버로 옮길 때 실제 도메인 주소와 SMTP 릴레이로 바꾼다**.
- 스팸 방지: 사람에게 보이지 않는 입력란 `kp_website`(honeypot)가 채워지면 스팸으로 처리(`plugins/kapla-shop-tweaks`). 공개 전에 Cloudflare Turnstile 추가를 검토한다.

### 확인한 것

- 화면에서 동의를 체크하고 보내면 「ありがとうございます。メッセージは送信されました。」가 표시되고, Local의 Mailpit에 관리자 메일과 자동 회신이 도착했다.
- honeypot 입력란을 채운 요청은 `status=spam`으로 막혔고 메일이 가지 않았다. 입력란이 비어 있으면 `mail_sent`.
- 참고: 동의 체크박스는 사용자가 실제로 눌러야 보내기 버튼이 활성화된다(스크립트로 값만 바꾸면 활성화되지 않음).
- 주문 메일(관리자·고객)도 같은 Mailpit에 도착했다(주문 #37, #38). 로컬 Mailpit 주소는 `http://localhost:10000`.

## SEO (`scripts/seed-seo.php`)

- 메인 페이지에 `<title>`, `description`, canonical, OGP(`og:*`), `twitter:card`가 출력된다. 메인 설명문은 일본어로 설정했다.
- WordPress 기본 사이트맵: `/wp-sitemap.xml` (200).
- **데모 기간에는 `noindex, nofollow`**(`blog_public = 0`)로 둔다. 실제로 공개해 검색에 노출할 때는 WordPress 설정의 「検索エンジンがサイトをインデックスしないようにする」를 끈다.

### 남은 것

- OGP 이미지 지정(영상에서 추출한 정지 이미지 사용 예정), `twitter:card`를 `summary_large_image`로 변경
- 홈서버 공개 주소로 canonical·사이트맵 URL이 바뀌는지 이전 후 확인
- 문의 폼 보낸 사람 주소와 SMTP 릴레이(홈서버)
