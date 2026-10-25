"""AI로 만든 상품 이미지를 규격(4:5, 가로 최대 1600px, JPG)에 맞춰 seed/images/ 에 저장한다.

원본이 1600px보다 작으면 늘리지 않고 그대로 쓴다(늘려도 선명해지지 않고 용량만 커진다).

사용법: seed/inbox/ 에 원본(png/jpg/webp)을 넣고 실행한다.
  파일명은 SKU로 시작해야 한다. 예) KP-OIL-SALAR.png (대표 이미지), KP-OIL-SALAR-2.png (갤러리 2번째)
  결과: seed/images/KP-OIL-SALAR.jpg, seed/images/KP-OIL-SALAR-2.jpg
"""
import re
import sys
from pathlib import Path

from PIL import Image, ImageOps

ROOT = Path(__file__).resolve().parent.parent
INBOX = ROOT / "seed" / "inbox"
OUT = ROOT / "seed" / "images"

SKUS = [
    "KP-OIL-SALAR", "KP-OIL-DUSK", "KP-OIL-CEDAR",
    "KP-HAND-SALAR", "KP-HAND-DUSK",
    "KP-DIFF-SALAR", "KP-DIFF-CEDAR", "KP-CAR-DUSK",
    "KP-CANDLE-SALAR", "KP-CANDLE-DUSK",
    "KP-GIFT-TRIAL", "KP-GIFT-SET",
]
TARGET = (1600, 2000)
MIN_SIZE = (1000, 1250)
EXTS = {".png", ".jpg", ".jpeg", ".webp"}


def match_sku(stem: str):
    """파일명에서 (SKU, 번호)를 찾는다. 번호가 없으면 대표 이미지(1)."""
    upper = stem.upper().replace("_", "-")
    # 긴 SKU부터 비교(KP-GIFT-SET 등이 다른 이름의 앞부분과 겹치지 않도록)
    for sku in sorted(SKUS, key=len, reverse=True):
        if upper == sku:
            return sku, 1
        m = re.fullmatch(re.escape(sku) + r"-(\d+)", upper)
        if m:
            return sku, int(m.group(1))
    return None, None


def convert(src: Path, dest: Path) -> str:
    img = Image.open(src)
    img = ImageOps.exif_transpose(img).convert("RGB")
    note = []
    if img.width < MIN_SIZE[0] or img.height < MIN_SIZE[1]:
        note.append(f"작음({img.width}x{img.height})")
    # 4:5로 가운데 기준 자르기
    want = TARGET[0] / TARGET[1]
    have = img.width / img.height
    if abs(have - want) > 0.005:
        if have > want:
            new_w = round(img.height * want)
            left = (img.width - new_w) // 2
            img = img.crop((left, 0, left + new_w, img.height))
        else:
            new_h = round(img.width / want)
            top = (img.height - new_h) // 2
            img = img.crop((0, top, img.width, top + new_h))
        note.append("4:5로 자름")
    if img.width > TARGET[0]:
        img = img.resize(TARGET, Image.LANCZOS)
    quality = 88
    while True:
        img.save(dest, "JPEG", quality=quality, optimize=True, progressive=True)
        if dest.stat().st_size <= 600 * 1024 or quality <= 72:
            break
        quality -= 4
    note.append(f"{dest.stat().st_size // 1024}KB(q{quality})")
    return ", ".join(note)


def main() -> int:
    sys.stdout.reconfigure(encoding="utf-8")
    INBOX.mkdir(parents=True, exist_ok=True)
    OUT.mkdir(parents=True, exist_ok=True)
    done = set()
    skipped = []
    for src in sorted(INBOX.iterdir()):
        if src.suffix.lower() not in EXTS:
            continue
        sku, n = match_sku(src.stem)
        if not sku:
            skipped.append(src.name)
            continue
        dest = OUT / (f"{sku}.jpg" if n == 1 else f"{sku}-{n}.jpg")
        print(f"{src.name} -> {dest.name}: {convert(src, dest)}")
        if n == 1:
            done.add(sku)
    for name in skipped:
        print(f"건너뜀(SKU로 시작하지 않음): {name}")
    have = {sku for sku in SKUS if (OUT / f"{sku}.jpg").exists()}
    missing = [s for s in SKUS if s not in have]
    print(f"\n대표 이미지 {len(have)}/{len(SKUS)}개 준비됨")
    if missing:
        print("없는 SKU: " + ", ".join(missing))
    return 0


if __name__ == "__main__":
    sys.exit(main())
