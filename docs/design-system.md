# 디자인 시스템

> 갱신: 2026-10-28 · 방향 "A. Warm Editorial": 크림 바탕, 테라코타 포인트, 명조 제목, 가는 선과 넉넉한 여백.

## 목차

1. [글꼴](#글꼴)
2. [색](#색)
3. [구성 요소](#구성-요소)
4. [페이지 구성](#페이지-구성)
5. [영상과 이미지](#영상과-이미지)
6. [구현 메모](#구현-메모)

## 글꼴

모두 SIL Open Font License(OFL)이고 `themes/kapla/assets/fonts/`에서 직접 배포한다(출처: `google/fonts`, 라이선스 파일 `OFL-*.txt`).

| 용도 | 글꼴 | 크기 | 비고 |
|---|---|---|---|
| 본문 | Zen Kaku Gothic New 400 / 500 | 각 약 450KB | 굵은 글씨는 500만 쓴다(`strong`, 표 머리글, 요약 줄) |
| 제목·메뉴 | Shippori Mincho 500 | 약 690KB | 헤더 메뉴는 16px, 자간 0.14em |
| 로고·영문 라벨·가격·날짜 | Cormorant Garamond 600 | 약 35KB | 영문·숫자만. 숫자는 `lining-nums`. 로고 `KAPLA`는 대문자 + 자간 0.3em을 CSS로 처리(사이트 이름은 `Kapla`) |

- **서브셋**: ASCII, 라틴 보조, 기호, 히라가나, 가타카나, 전각 기호, JIS X 0208 제1수준 한자(약 2,960자). 사이트에 쓰인 한자 309자는 모두 들어 있다. 범위 밖 글자는 OS 글꼴로 표시된다.
- **표시**: 모두 `font-display: swap`이라 로딩 전에는 OS 글꼴로 먼저 보인다. 본문에는 `font-feature-settings: "palt"`와 `letter-spacing: 0.03em`을 적용했다.
- **다시 만들기**: `python scripts/build-fonts.py <원본 폴더> themes/kapla/assets/fonts [logo|mincho|zen]` (fonttools, brotli 필요)

## 색

theme.json 팔레트.

| 이름 | 값 | 용도 |
|---|---|---|
| cream | `#F4EEE4` | 페이지 바탕 |
| paper | `#FBF8F2` | 카드·입력란 바탕 |
| sand / stone | `#E6DCCB` / `#CDBFA8` | 이미지 바탕, 보조 |
| ink / ink-soft | `#2B211B` / `#6B5A4D` | 본문 / 보조 글씨(대비 5.7:1) |
| terracotta / terracotta-deep | `#B07650` / `#8A5636` | 포인트·버튼·링크(대비 5.25:1) |
| night | `#1E1512` | 푸터 |

가는 선(장식)은 `#D9CCB6`, 입력란 테두리는 `#9A8A78`(바탕 대비 3:1 이상).

## 구성 요소

| 요소 | 규칙 |
|---|---|
| 버튼 | 기본은 테라코타 채움(마우스를 올리면 ink). 상품 목록·추천 상품의 카트 버튼은 가는 테두리(마우스를 올리면 채워짐) |
| 입력란 | 높이 44px(로그인 폼은 48px), 테두리 1px, 모서리 2px, 포커스 시 테라코타 링. WooCommerce 폼·리뷰 폼·문의 폼(CF7)·카트/결제 블록에 공통 |
| 로그인 폼 | 가운데 카드. 라벨·체크박스·링크 문구는 14px로 통일하고, 로그인 버튼과 「ログイン状態を保存」를 한 줄 가운데 정렬 |
| 표 | 줄무늬 없이 위아래 굵은 선과 가는 구분선(特定商取引法, 店舗). 모바일에서는 세로로 쌓인다 |
| 아코디언 | 자주 묻는 질문. `core/details` 블록 + 오른쪽 +/− 표시 |
| 헤더 | 왼쪽 일본어 텍스트 메뉴, 가운데 로고, 오른쪽 가는 선 아이콘(계정·카트). 메뉴에 마우스를 올리면 밑줄이 왼쪽에서 그려진다. 메인에서는 영상 위에 투명하게 겹친다 |
| 상품 카드 | 4:5 이미지, 명조 상품명, Cormorant 가격. 4열 → 3열(1023px 이하) → 2열(719px 이하). 샵 목록·메인 추천·관련 상품·검색 결과가 같은 CSS를 쓴다 |
| 장바구니 담기 | 버튼을 누르면 오른쪽에서 미니 카트가 열리고, 버튼 문구는 「追加済み（N点）」로 바뀐다 |
| 상품 상세 | 2단(이미지 \| 정보), 모바일에서 1단. 확대(줌)와 돋보기 버튼은 쓰지 않는다. 탭은 밑줄만 |
| 안내 배너 | 클래식(`.woocommerce-message`)과 블록(`.wc-block-components-notice-banner`) 모두 가는 테두리와 왼쪽 테라코타 선 |

## 페이지 구성

- **페이지 머리말**: `templates/page.html`은 고정페이지 맨 위에 영문 라벨, 제목, 리드문을 보여 준다. 라벨과 리드문은 커스텀 필드 `kapla_eyebrow`, `kapla_lead`에서 읽고(비어 있으면 숨김), 시드(`seed-pages.php`)가 값을 넣는다. 관리자 화면에서는 페이지 편집의 「設定 > カスタムフィールド」에서 바꾼다.
- **브랜드 페이지**: `templates/page-brand.html`(슬러그 `brand`)은 머리말 없이 본문만 보여 준다. 본문은 `patterns/brand-page.php`를 시드가 페이지 내용으로 저장한 것이라 관리자에서 직접 편집할 수 있다. 향 카드는 CSS 그라데이션(`.kapla-swatch--salar|dusk|cedar`)이다.
- **파비콘**: `themes/kapla/assets/images/favicon.png`(512×512, 진한 갈색 바탕의 크림색 "K")를 `scripts/seed-branding.php`가 사이트 아이콘으로 설정한다. 다시 만들기: `python scripts/build-favicon.py`

## 영상과 이미지

| 파일 | 내용 |
|---|---|
| `themes/kapla/assets/video/hero.mp4` | 메인 히어로 영상(약 1.4MB, 1080p, 음성 제거, H.264) |
| `themes/kapla/assets/video/hero-poster.jpg` | 영상 로딩 중·모션 축소 설정에서 보이는 정지 이미지(약 70KB) |
| `themes/kapla/assets/images/og-image.jpg` | OGP(SNS 공유) 이미지 1200×630 |
| `seed/images/<SKU>.jpg` | 상품 사진 12점(AI 생성, 4:5) |

- 히어로 영상은 저작권 문제없이 쓸 수 있는 원본을 줄여 만든 것이다. 원본은 Git에 두지 않는다. 다시 만들기: `.\scripts\build-video.ps1 -Ffmpeg <ffmpeg.exe 경로>`
- 상품 사진은 `seed/images/<SKU>.jpg`로 두고 `.\scripts\seed.ps1`을 실행하면 상품에 붙는다. 규격과 프롬프트는 [ai-image-prompts.md](ai-image-prompts.md). 실제 상품 사진으로 바꿀 때도 비율(4:5)과 배경색(sand)은 그대로 쓸 수 있다.

## 구현 메모

- **상품 상세·목록·검색은 블록 템플릿**이다(`templates/single-product.html`, `archive-product.html`, `search.html`). 갤러리·장바구니 폼·탭의 안쪽 HTML은 WooCommerce 블록이 클래식과 같은 마크업으로 출력하므로 `form.cart`, `.woocommerce-tabs` 같은 클래스는 그대로 쓴다. 바깥쪽 `div.product`는 없으므로 선택자는 `.kapla-product …`.
- SKU 표시는 `product-sku` 블록의 `prefix` 속성으로 「商品コード: 」를 붙였다.
- 관련 상품 패턴은 코어가 `is-flex-container columns-5`의 `li` 폭을 20%로 계산하므로, 격자에 맡기도록 선택자를 구체적으로 덮어썼다.
- WooCommerce 기본 CSS의 우선순위가 높아서 상품 목록·상세·탭 규칙은 `body.woocommerce …`처럼 선택자를 더 구체적으로 썼다.
- WooCommerce가 블록 템플릿에서 상품 이미지 줌을 강제로 켜기 때문에, `functions.php`의 줌 끄기 필터는 우선순위 999로 걸었다.
- 스타일시트는 `style.css` 하나(섹션 0~13)이고 버전은 0.4.13. 브라우저가 이전 CSS를 기억하면 강제 새로고침(Ctrl+F5)한다.
