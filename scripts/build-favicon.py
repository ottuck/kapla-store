"""Kapla のサイトアイコン(ファビコン)を作る: 濃い茶色の地にクリーム色の「K」(Cormorant Garamond)。

使い方: python scripts/build-favicon.py   (Pillow と fonttools+brotli が入った Python で)
出力: themes/kapla/assets/images/favicon.png (512x512)。WordPress のサイトアイコンは seed-branding.php が設定する。
"""
import io
from pathlib import Path

from fontTools.ttLib import TTFont
from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parent.parent
FONT = ROOT / "themes" / "kapla" / "assets" / "fonts" / "CormorantGaramond-SemiBold.woff2"
OUT = ROOT / "themes" / "kapla" / "assets" / "images" / "favicon.png"
SIZE = 512
BG = (43, 33, 27)       # ink  #2B211B
FG = (251, 248, 242)    # paper #FBF8F2
ACCENT = (176, 118, 80)  # terracotta #B07650


def main() -> None:
    font = TTFont(str(FONT))
    font.flavor = None
    buf = io.BytesIO()
    font.save(buf)
    buf.seek(0)
    glyph_font = ImageFont.truetype(buf, 400)

    img = Image.new("RGB", (SIZE, SIZE), BG)
    d = ImageDraw.Draw(img)
    # 「K」を中央に置く(字形の外接矩形で中央揃え)
    box = d.textbbox((0, 0), "K", font=glyph_font)
    w, h = box[2] - box[0], box[3] - box[1]
    x = (SIZE - w) / 2 - box[0]
    y = (SIZE - h) / 2 - box[1] - 8
    d.text((x, y), "K", font=glyph_font, fill=FG)
    # 足元に細いテラコッタの線
    d.rectangle(((SIZE - 120) / 2, SIZE - 96, (SIZE + 120) / 2, SIZE - 92), fill=ACCENT)
    OUT.parent.mkdir(parents=True, exist_ok=True)
    img.save(OUT, optimize=True)
    print(OUT, OUT.stat().st_size, "bytes")


if __name__ == "__main__":
    main()
