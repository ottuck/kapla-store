# Kapla Store

가상의 향수·화장품 브랜드 "Kapla"의 온라인 숍을 **WordPress + WooCommerce 블록 테마**로 다시 만든 데모 사이트입니다. 2022년 3인 팀 프로젝트(Java/Spring/Oracle)의 리메이크이며, 일본 소규모 사업자용 숍을 가정해 일본어 UI, 엔화(JPY), 税込 표시로 구성했습니다. 결제는 Stripe 테스트 모드만 사용하고 실제 판매는 하지 않습니다.

## 구조

```
themes/kapla/              블록 테마 (theme.json, 템플릿, 패턴, 글꼴, 히어로 영상)
plugins/kapla-shop-tweaks/ 송료·결제 표시·스팸 방지 등 작은 보정 플러그인
scripts/                   WP-CLI 래퍼, 시드(페이지·상품·배송·문의폼·SEO), 자산 생성 스크립트
seed/images/               상품 이미지(AI로 만든 사진 12종)
docs/                      설계·결정·점검 기록, 포트폴리오 소개문과 화면 캡처(docs/portfolio/)
tools/capture/             화면 캡처 스크립트(Chrome을 조작해 docs/portfolio/screenshots 에 저장)
```

## 로컬 실행

전제: Windows, [Local](https://localwp.com/)에 `kapla` 사이트가 있고(PHP 8.5, MySQL 8.4), WooCommerce·Contact Form 7·SEO SIMPLE PACK·Stripe 플러그인이 설치되어 있어야 합니다.

1. 테마와 플러그인을 Local 사이트에 연결합니다(정션).

```powershell
$wp = "$env:USERPROFILE\Local Sites\kapla\app\public\wp-content"
New-Item -ItemType Junction -Path "$wp\themes\kapla" -Target "$PWD\themes\kapla"
New-Item -ItemType Junction -Path "$wp\plugins\kapla-shop-tweaks" -Target "$PWD\plugins\kapla-shop-tweaks"
```

2. 관리자 화면에서 테마 `Kapla`와 플러그인 `kapla-shop-tweaks`를 활성화합니다.
3. 페이지, 상품, 배송, 문의 폼, SEO를 한 번에 만듭니다(여러 번 실행해도 안전합니다).

```powershell
.\scripts\seed.ps1
```

4. 테마 패턴을 고친 뒤에는 캐시를 지웁니다: `.\scripts\clear-cache.ps1`
5. Stripe 테스트 키 입력: `.\scripts\stripe-test-keys.ps1` (`pk_test_`/`sk_test_`만 받습니다. 키는 DB에만 저장됩니다)

WP-CLI는 `.\scripts\wp.ps1 <명령>`으로 실행합니다. 상품 이미지를 바꾸는 방법은 `docs/ai-image-prompts.md`를 참고하세요.

## 문서

- [원본 분석과 리메이크 결정](docs/original-analysis.md)
- [결제·배송 설정과 테스트 현황](docs/payments.md)
- [문의 폼과 SEO](docs/contact-seo.md)
- [글꼴·영상·이미지 자산](docs/assets.md)
- [상품 이미지 만들기](docs/ai-image-prompts.md)
- [디자인 시스템(글꼴·색·구성 요소)](docs/design-system.md)
- [화면 품질 점검 기록](docs/quality-check.md)
- [호스팅 사전 조사](docs/hosting-reference.md)

## 포트폴리오용 자료

- [고객용 일본어 소개문](docs/portfolio/kapla-intro-ja.md)
- 화면 캡처(PC·스마트폰 15장): `docs/portfolio/screenshots/`. 다시 찍으려면 `cd tools/capture && npm install && npm run capture` (Chrome 필요, 로컬 `kapla.local`을 촬영합니다)
