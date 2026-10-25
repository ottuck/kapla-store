# 상품 이미지 AI 생성 가이드

> 생성한 이미지는 `seed/inbox/`에 넣고 변환 스크립트를 실행한 뒤 `.\scripts\seed.ps1`을 실행하면 상품에 대표 이미지가 붙는다.

## 현재 적용된 이미지

12종 모두 AI로 만든 사진(1122 × 1402 px, 4:5)이 들어 있다. 공통 스타일(크림 배경, 왼쪽 석양빛, 글자·로고 없음)에 향마다 소재가 다르다(SALAR 소금 결정 / DUSK 석양빛과 돌 / CEDAR 삼나무 가지). 원본이 1600px보다 작아서 `prepare-images.py`는 늘리지 않고 그대로 쓴다. 상품 상세의 큰 이미지는 `woocommerce_single_image_width` 1000px로 만든다.

처음에 코드로 그린 일러스트(`scripts/build-product-art.py`)는 `seed/inbox/_illustration/`에 보관해 두었다. 이 스크립트를 다시 실행하면 `seed/inbox/`에 같은 이름의 PNG가 생겨 사진과 겹치니, 실행하기 전에 확인한다.

## 작업 순서

1. AI로 생성한 이미지를 `seed/inbox/`에 넣는다. 파일명은 SKU로 시작한다(PNG/JPG/WebP 모두 가능, 대소문자·`_` 무관).
   - 대표 이미지: `KP-OIL-SALAR.png`
   - 갤러리(선택): `KP-OIL-SALAR-2.png`, `KP-OIL-SALAR-3.png` … (상품 상세에서 썸네일로 보인다)
2. 변환: 4:5로 가운데 기준 자르고, 가로가 1600px보다 크면 1600×2000으로 줄여(작으면 그대로) JPG(약 600KB 이하)로 `seed/images/`에 저장한다.

```powershell
.\.venv-tools\Scripts\python scripts\prepare-images.py
```

   `.venv-tools`가 없으면 한 번만 만든다: `python -m venv .venv-tools` 그리고 `.\.venv-tools\Scripts\python -m pip install pillow`

3. 반영: `.\scripts\seed.ps1` — 상품에 이미지가 붙는다. 이미지를 바꿔서 다시 실행하면 이전 이미지는 지워지고 새 이미지로 교체된다(같은 이미지는 중복해서 올리지 않는다).
4. 상품 카드는 4:5(세로) 비율, 폭 600px로 만들어진다(`seed-products.php`가 설정). 이 설정을 바꾸면 `.\scripts\wp.ps1 media regenerate --yes`로 썸네일을 다시 만든다.

`seed/inbox/`(원본)는 Git에 올리지 않고, `seed/images/`(변환 결과)만 올린다.

## 규격

| 항목 | 값 |
|---|---|
| 비율 / 크기 | 4:5 세로, 1600 × 2000 px 권장 (최소 1000 × 1250. 더 작으면 늘리지 않는다) |
| 형식 | JPG (품질 85~90, 파일당 500KB 안팎까지 줄이면 좋다) |
| 파일명 | SKU 그대로: `KP-OIL-SALAR.jpg` (대문자, 하이픈) |
| 금지 | 실제 브랜드 로고·상표, 이미지 속 글자(AI는 글자를 자주 깨뜨린다), 사람 얼굴 |

## 공통 스타일 (모든 프롬프트 앞에 붙인다)

```
Editorial product photograph, soft warm studio light from the left like late sunset,
seamless cream backdrop (#F4EEE4), subtle terracotta (#B07650) shadow tones,
minimal composition, generous negative space, shallow depth of field, matte surfaces,
no text, no logo, no brand name, no people, 4:5 vertical
```

## 상품별 프롬프트

| SKU | 상품 | 프롬프트 (공통 스타일 뒤에 이어 붙인다) |
|---|---|---|
| KP-OIL-SALAR | SALAR 塩湖 오일 퍼퓸 | a 10ml amber glass roll-on perfume bottle with a plain blank paper tag, standing on a thin sheet of white salt crystals, pale blue-pink reflection behind |
| KP-OIL-DUSK | DUSK 夕景 오일 퍼퓸 | a 10ml dark amber glass roll-on bottle with a plain blank paper tag, on warm sand-colored stone, deep orange and plum glow at the horizon line |
| KP-OIL-CEDAR | CEDAR 杉 오일 퍼퓸 | a 10ml amber glass roll-on bottle with a plain blank paper tag, next to a small cedar wood branch and moss, soft green-brown tones |
| KP-HAND-SALAR | SALAR 塩湖 핸드 퍼퓸 | a 30ml matte white aluminum tube of hand cream, no print, lying on white salt crystals |
| KP-HAND-DUSK | DUSK 夕景 핸드 퍼퓸 | a 30ml matte terracotta aluminum tube of hand cream, no print, on warm stone |
| KP-DIFF-SALAR | SALAR 塩湖 리드 디퓨저 | a 100ml clear frosted glass diffuser bottle with thin natural reed sticks, plain blank label, pale salt-white backdrop |
| KP-DIFF-CEDAR | CEDAR 杉 리드 디퓨저 | a 100ml dark green glass diffuser bottle with reed sticks, plain blank label, next to a cedar branch |
| KP-CAR-DUSK | DUSK 夕景 카 디퓨저 | a small wooden-cap car vent diffuser clip, terracotta tone, on a dark leather surface |
| KP-CANDLE-SALAR | SALAR 塩湖 캔들 | a 120g soy candle in a cream ceramic jar with matte lid, unlit, plain blank label, salt crystals around |
| KP-CANDLE-DUSK | DUSK 夕景 캔들 | a 120g soy candle in a terracotta ceramic jar, lit with a small flame, warm glow on the wall |
| KP-GIFT-TRIAL | 트라이얼 세트 | three 3ml amber glass roll-on mini bottles lined up on a cream paper gift box, plain blank tags |
| KP-GIFT-SET | 기프트 세트 | a cream gift box open, containing a 10ml amber roll-on bottle and a small ceramic candle jar, kraft paper ribbon, plain blank labels |

## 라벨 처리

AI가 만든 병에는 글자를 넣지 않는다(위 프롬프트는 모두 "blank label"). Kapla 로고나 향 이름은 나중에 이미지 편집 도구에서 올리거나, 사이트 디자인(상품 카드의 텍스트)로 보여 준다.

## 히어로 영상

영상은 이미 확보했다(`assets/source/mainVideo.mp4`). 여기서는 AI 이미지가 필요 없다. 정지 이미지(poster)는 영상에서 한 프레임을 추출해 만든다.
