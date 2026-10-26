# Kapla Store — 작업 안내 (Claude용)

가상의 향수·화장품 브랜드 Kapla의 숍을 WordPress + WooCommerce **블록 테마**로 다시 만든 포트폴리오 데모다(일본 외주 영업용). 구조와 로컬 실행은 `README.md`, 설계 기록은 `docs/`에 있다.

## 사용자와 작업 방식

- 보고, 진행 안내, 질문은 **한국어**로 한다. 사이트 UI와 콘텐츠는 일본어다.
- 사용자는 PHP/WordPress 초보이고 "AI가 구현하고 사용자는 결과물과 관리자 화면만 확인"하는 방식이다. 명령은 그대로 붙여 넣어 실행할 수 있게 주고, 결과를 확인해서 보고한다.
- 일정은 짧게 잡는다(하루이틀 단위). 사용자가 이미 정한 것은 다시 묻거나 뒤집지 않는다.

## 확정된 결정

- 일본어 UI, 통화 JPY(소수점 0자리), 가격은 税込 표시, 消費税 10%. 배송은 일본만(전국 500엔, 8,000엔 이상 무료).
- 결제는 **Stripe 테스트 모드만** 쓴다(실제 판매 없음). 실제 키(`sk_live_`, `pk_live_`)는 어디에도 넣지 않는다.
- 원본(2022~23 팀 프로젝트)의 상품 이미지, 모델 사진, 목업 속 개인정보(전화, 이메일, 사업자번호)는 **재사용 금지**. 상품 이미지는 사용자가 AI로 만든 것이다. 히어로의 일몰 영상은 사용자가 저작권 문제없이 구한 것이라 그대로 쓴다.
- 브랜드명은 영문 Kapla. 디자인은 Warm Editorial(크림 바탕, 테라코타 `#B07650` 포인트, 세리프 제목).
- PHP는 **8.5.12 유지**, WordPress 7.1.3, WooCommerce 11.2.1. 테마는 블록 방식(체크아웃에 문제가 생기면 클래식으로 폴백).
- 호스팅: Local(LocalWP)에서 결제까지 완성 → **M1 맥북 홈서버**(Docker Compose + Cloudflare Tunnel)로 이전해 공개한다. 월 약 1,000엔 이상의 추가 지출 없음, 유료 공유호스팅은 계약하지 않는다. `docs/hosting-reference.md`는 나중의 납품용 사전 조사일 뿐 결정이 아니다.
- 사용자의 다른 서비스는 AWS Lightsail에서 돌고 있다. **기존 Lightsail 인스턴스에 WordPress를 넣자고 제안하지 않는다**(RAM 2GB에 여유가 거의 없고 실계좌를 쓰는 서비스가 같이 돈다). WordPress는 MySQL/MariaDB만 지원하므로 기존 Postgres도 쓸 수 없다.

## 홈서버 이전 — 어디를 보나

**기준 문서는 `infra/home-server/README.md`** 이고, 파일은 같은 폴더의 `compose.home.yaml`, `.env.example`이다. 순서: 서버 준비 → 로컬 내보내기 → 가져오기와 주소 치환 → Tunnel 연결 → Cloudflare 설정 → cron·백업.

- 공개 주소 후보: `https://kapla.portfolio-hub.dev`. Tunnel 이름은 `<tunnel-name>`, 서비스 포트는 `127.0.0.1:8081`.
- 홈서버 공통 인프라 원문은 사용자의 `Downloads/<infra-notes>.md`(Windows PC)다. 그 요지: MacBook Air M1(RAM 8GB, SSD 256GB), Docker, Tailscale(관리용), Cloudflare Tunnel(공개용), 프로젝트별 `~/server/apps/<project>/` + 독립 Compose, 포트는 127.0.0.1에만 bind, 컨테이너마다 메모리 제한, 공유기 포트포워딩 없음.
- `docs/home-server.md`는 사용자가 **일부러 지운 파일**이다. 복원하지 않는다.
- 이전 전 확인이 남아 있다(Mac에서 직접 확인): `cloudflared` 자동 시작, Docker 로그인 시 실행, 절전 방지, 재부팅 후 자동 복구, Tunnel `<tunnel-name>`이 **Mac에서** 실행되는지, `portfolio-hub.dev` DNS가 Cloudflare에 있는지.
- 검증 상태: Compose와 이전 절차는 Windows Docker(amd64)의 임시 스택에서 확인했다. **M1(arm64) 실기, 실제 Cloudflare 연결, SMTP는 아직 확인하지 않았다.** 확인하지 않은 부분을 확인한 것처럼 보고하지 않는다.
- 아직 없는 작업: 주문·문의 메일용 SMTP 릴레이, Stripe 웹훅 등록(공개 주소가 생긴 뒤), 공개 후 Lighthouse 측정.

## 주의사항 (반드시 지킨다)

- **`docker compose down -v` 금지.** `-v`는 DB와 uploads 볼륨을 지운다(주문 데이터는 DB 백업으로만 보존된다). 중지는 `docker compose down`만 쓴다.
- `~/.cloudflared/`(Tunnel 인증 정보), 다른 프로젝트의 컨테이너와 `~/server/apps/` 아래 다른 폴더, 공유기·Cloudflare 계정 설정은 사용자 확인 없이 바꾸지 않는다. 작업은 `~/server/apps/kapla-store` 안에서 한다.
- `.env`, DB 덤프, uploads 압축본, Stripe 키는 Git에 올리지 않는다(`.gitignore` 대상). 비밀값을 채팅에 출력하지 않는다.
- 이 Mac은 상시 서버이기도 하다. 재부팅, 절전 설정 변경, 다른 서비스를 멈출 수 있는 명령, 메모리를 크게 쓰는 작업은 먼저 알리고 확인한다(RAM 8GB가 가장 큰 제약).
- 테마와 `plugins/kapla-shop-tweaks`는 서버에서 읽기 전용으로 마운트된다. **코드는 로컬에서 고치고 Git으로 배포**한다(서버에서 직접 수정하지 않는다). 주문 로직은 테마가 아니라 플러그인에 둔다.
- 테마 패턴 파일을 고친 뒤에는 캐시를 지운다: 로컬은 `.\scripts\clear-cache.ps1`, 서버는 `wp transient delete --all`.
- 쿼리 문자열에 `?m=1`처럼 WordPress 예약어(`m`, `p`, `s` 등)를 쓰면 404가 나온다. 테스트할 때 피한다.
- Windows 로컬의 WP-CLI는 `.\scripts\wp.ps1 <명령>`이고, `wp db export`는 쓸 수 없다(`mysqldump`가 PATH에 없음). 내보내기 명령은 `infra/home-server/README.md` 2장을 따른다.
- 커밋 메시지와 PR 설명 끝의 공동 작성자 표기는 세션이 지정한 형식을 따른다.
