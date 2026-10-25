# 글꼴·영상·이미지 자산

> 갱신: 2026-10-26

## 글꼴

| 용도 | 글꼴 | 방식 | 크기 |
|---|---|---|---|
| 제목 | Shippori Mincho Medium (500) | 웹 글꼴(자체 호스팅), 일본어 서브셋 | 약 690KB |
| 로고 `Kapla` | Lobster (400) | 웹 글꼴(자체 호스팅), 영문만 | 약 33KB |
| 본문 | OS 기본 고딕 (Hiragino Sans, Yu Gothic UI, Meiryo, Noto Sans CJK JP 순) | 시스템 글꼴 | 0 |

- 두 웹 글꼴 모두 SIL Open Font License(OFL). 라이선스 파일은 `themes/kapla/assets/fonts/OFL-*.txt`. 출처는 `google/fonts` 저장소.
- 일본어 글꼴은 한자 때문에 무겁기 때문에, 제목에만 쓰고 본문은 OS 고딕으로 둔다. 모든 글꼴은 `font-display: swap`이라 로딩 전에는 OS 글꼴로 먼저 보인다.
- 서브셋 범위: ASCII, 라틴 보조, 기호, 히라가나, 가타카나, 전각 기호, JIS X 0208 **제1수준 한자**(약 2,960자). 범위 밖 글자는 OS 명조로 표시된다. 사이트 본문·상품명에 쓰인 한자 309자는 모두 포함됨을 확인했다.
- 다시 만들기: `python scripts/build-fonts.py <원본 폴더> themes/kapla/assets/fonts` (fonttools, brotli 필요).
- 본문도 웹 글꼴로 통일하고 싶으면 Noto Sans JP를 같은 방식으로 추가하면 된다(약 1MB 추가).

## 히어로 영상

| 파일 | 내용 |
|---|---|
| `assets/source/mainVideo.mp4` | 원본(6.8MB, 1080p, 21.8초, 음성 포함). 보존용, **Git에는 올리지 않음**(로컬에만 있음) |
| `themes/kapla/assets/video/hero.mp4` | 사이트용(약 1.4MB, 1080p, 음성 제거, H.264) |
| `themes/kapla/assets/video/hero-poster.jpg` | 영상 로딩 중·모션 축소 설정에서 보이는 정지 이미지(약 70KB) |
| `themes/kapla/assets/images/og-image.jpg` | OGP(SNS 공유) 이미지 1200×630 |

- 다시 만들기: `.\scripts\build-video.ps1 -Ffmpeg <ffmpeg.exe 경로>`
- 원본 영상은 사용자가 저작권 문제없이 구한 것으로 확인했다. 출처·라이선스 표기는 사용자가 확인되는 대로 `docs/original-analysis.md`에 추가한다.

## 이미지

- 상품 이미지는 AI로 생성해 `seed/images/<SKU>.jpg`로 두고 `.\scripts\seed.ps1`을 실행하면 자동으로 붙는다(규격과 프롬프트: `docs/ai-image-prompts.md`).
