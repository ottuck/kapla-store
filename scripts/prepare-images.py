"""AI で作った商品画像を規格(4:5、横幅最大 1600px、JPG)に合わせて seed/images/ に保存する。

元画像が 1600px より小さければ拡大せずにそのまま使う(拡大しても鮮明にならず、容量が増えるだけのため)。

使い方: seed/inbox/ に元画像(png/jpg/webp)を入れて実行する。
  ファイル名は SKU で始める。例) KP-OIL-SALAR.png(メイン画像)、KP-OIL-SALAR-2.png(ギャラリーの 2 枚目)
  結果: seed/images/KP-OIL-SALAR.jpg、seed/images/KP-OIL-SALAR-2.jpg
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
    """ファイル名から (SKU, 番号) を取り出す。番号がなければメイン画像(1)。"""
    upper = stem.upper().replace("_", "-")
    # 長い SKU から比べる(KP-GIFT-SET などが別の名前の先頭部分と重ならないように)
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
        note.append(f"小さい({img.width}x{img.height})")
    # 4:5 に中央で切り抜く
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
        note.append("4:5 に切り抜き")
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
        print(f"スキップ(SKU で始まっていない): {name}")
    have = {sku for sku in SKUS if (OUT / f"{sku}.jpg").exists()}
    missing = [s for s in SKUS if s not in have]
    print(f"\nメイン画像 {len(have)}/{len(SKUS)} 点を用意しました")
    if missing:
        print("画像のない SKU: " + ", ".join(missing))
    return 0


if __name__ == "__main__":
    sys.exit(main())
