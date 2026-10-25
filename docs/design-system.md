# 디자인 시스템

> 갱신: 2026-10-26 · 방향 "A. Warm Editorial": 크림 바탕, 테라코타 포인트, 명조 제목, 가는 선과 넉넉한 여백.

## 글꼴 (모두 SIL OFL, 자체 호스팅, `themes/kapla/assets/fonts/`)

| 용도 | 글꼴 | 비고 |
|---|---|---|
| 본문 | Zen Kaku Gothic New Regular 400 / Medium 500 | 부드럽고 현대적인 고딕. 굵은 글씨는 Medium(500)만 쓴다(`strong`, 표 머리글, 요약 줄). 각 약 450KB |
| 제목·메뉴 | Shippori Mincho Medium 500 | 헤더 메뉴도 같은 글꼴. 약 690KB |
| 로고·영문 라벨·가격·날짜 | Cormorant Garamond SemiBold 600 | 영문·숫자만. 숫자는 `lining-nums`(Cormorant 기본은 작은 옛 숫자). 약 35KB |

- 일본어 글꼴은 JIS X 0208 제1수준 한자까지 서브셋. 범위 밖 글자는 OS 글꼴로 표시된다.
- 글꼴 다시 만들기: `scripts/build-fonts.py <원본 폴더> themes/kapla/assets/fonts [logo|mincho|zen]`
- 본문에 `font-feature-settings: "palt"`(가나 간격 조정)와 `letter-spacing: 0.03em`을 적용했다.

## 색 (theme.json 팔레트)

| 이름 | 값 | 용도 |
|---|---|---|
| cream | #F4EEE4 | 페이지 바탕 |
| paper | #FBF8F2 | 카드·입력란 바탕 |
| sand / stone | #E6DCCB / #CDBFA8 | 이미지 바탕, 보조 |
| ink / ink-soft | #2B211B / #6B5A4D | 본문 / 보조 글씨(대비 5.7:1) |
| terracotta / terracotta-deep | #B07650 / #8A5636 | 포인트·버튼·링크(대비 5.25:1) |
| night | #1E1512 | 푸터 |

가는 선(장식)은 `#D9CCB6`, 입력란 테두리는 `#9A8A78`(바탕 대비 3:1 이상).

## 구성 요소

- **버튼**: 기본은 테라코타(마우스 올리면 ink). 상품 목록·추천 상품의 카트 버튼은 가는 테두리(마우스 올리면 채워짐).
- **입력란**: 높이 44px, 테두리 1px, 모서리 2px, 포커스 시 테라코타 링. WooCommerce 폼·리뷰 폼·문의 폼(CF7)·카트/결제 블록에 공통 적용.
- **표**: 줄무늬 없이 위아래 굵은 선과 가는 구분선(특정상거래법, 店舗). 모바일에서는 세로로 쌓인다.
- **아코디언**: 자주 묻는 질문. `core/details` 블록 + 오른쪽 +/− 표시.
- **헤더**: 메뉴에 마우스를 올리면 밑줄이 왼쪽에서 그려진다. 메인에서는 영상 위에 투명하게 겹친다.
- **상품 카드**: 4:5 이미지, 명조 상품명, Cormorant 가격. 4열 → 3열(1023px 이하) → 2열(719px 이하). 샵 목록·메인 추천·관련 상품이 모두 `woocommerce/product-collection` 블록이라 같은 CSS를 쓴다.
- **상품 상세**: 2단(이미지 | 정보), 모바일에서 1단. 확대(줌)와 돋보기 버튼은 쓰지 않는다. 탭은 밑줄만.
- **안내 배너**: 클래식(`.woocommerce-message`)과 블록(`.wc-block-components-notice-banner`) 모두 가는 테두리와 왼쪽 테라코타 선.

## 페이지 머리말 (영문 라벨과 리드문)

`templates/page.html`은 고정페이지 맨 위에 영문 라벨, 제목, 리드문을 보여 준다. 라벨과 리드문은 고정페이지의 **커스텀 필드** `kapla_eyebrow`, `kapla_lead`에서 읽고(비어 있으면 숨김), 시드(`seed-pages.php`)가 값을 넣는다.
관리자 화면에서 바꾸려면 페이지 편집 화면의 「설정 > 커스텀 필드」(없으면 옵션에서 켠다)에서 수정한다.

## 브랜드 페이지

`templates/page-brand.html`(슬러그가 `brand`인 페이지에 자동 적용)은 머리말 없이 본문만 보여 준다. 본문은 `patterns/brand-page.php`(히어로, 스토리, 3가지 신념, 3가지 향, 버튼)를 시드가 그대로 페이지 내용으로 저장한 것이라 이후 관리자에서 직접 편집할 수 있다. 사진은 히어로 영상의 정지 이미지를 쓰고, 향 카드는 CSS 그라데이션(`.kapla-swatch--salar|dusk|cedar`)이다.

## 파비콘

`themes/kapla/assets/images/favicon.png`(512×512, 진한 갈색 바탕의 크림색 "K")를 `scripts/seed-branding.php`가 WordPress 사이트 아이콘으로 설정한다(`seed.ps1`에 포함). 이미지는 `python scripts/build-favicon.py`로 다시 만든다.

## 알아 둘 것

- **상품 상세·상품 목록은 블록 템플릿**이다(`templates/single-product.html`, `archive-product.html`; 예전의 `woocommerce/legacy-template` 껍데기는 버렸다). 갤러리·장바구니 폼·탭 같은 안쪽 HTML은 WooCommerce 블록이 클래식과 같은 마크업으로 출력하므로 그 부분의 클래스(`form.cart`, `.woocommerce-tabs` 등)는 그대로 쓴다. 바깥쪽 `div.product`는 없으므로 선택자는 `.kapla-product …`.
- 블록 템플릿의 SKU 표시는 `product-sku` 블록의 `prefix` 속성으로 일본어(「商品コード: 」)로 바꿨다.
- 관련 상품 패턴은 코어가 `is-flex-container columns-5`의 `li` 폭을 20%로 계산하므로, 격자에 맡기도록 선택자를 구체적으로 덮어썼다.
- WooCommerce 기본 CSS의 우선순위가 높아서 상품 목록·상세·탭 규칙은 `body.woocommerce …`, `.single-product.woocommerce …`처럼 선택자를 더 구체적으로 썼다.
- WooCommerce가 블록 템플릿에서 상품 이미지 줌을 강제로 켜기 때문에, `functions.php`의 줌 끄기 필터는 우선순위 999로 걸었다.
- 스타일시트는 `style.css` 하나(섹션 0~13)이고 버전은 0.4.5. 브라우저가 이전 CSS를 기억하면 강제 새로고침(Ctrl+F5)한다.
- 실제 상품 사진이 들어오면 이미지 비율(4:5)과 배경색(sand)은 그대로 쓸 수 있다.
