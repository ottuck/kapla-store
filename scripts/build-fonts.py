"""日本語見出し用フォントを軽量化(サブセット化)して woff2 にする。

使い方 (fonttools と brotli が入った Python で):
    python scripts/build-fonts.py <元フォントの置き場> <出力先 themes/kapla/assets/fonts>

元フォント: CormorantGaramond[wght].ttf (wght 600 に固定して使う), ShipporiMincho-Medium.ttf (どちらも SIL OFL, google/fonts)
収録文字: ASCII・ラテン補助・記号・ひらがな・カタカナ・全角記号・JIS X 0208 第1水準の漢字(約2,960字)。
収録外の文字はブラウザが次のフォント(OS の明朝/ゴシック)で表示する。
"""

import sys
from pathlib import Path

from fontTools import subset
from fontTools.ttLib import TTFont
from fontTools.varLib import instancer


def jis_x_0208_kanji() -> list[int]:
    """JIS X 0208 第1水準の漢字(約2,960字)。第2水準まで入れると約1.5MBになるため、見出しには第1水準で足りる前提にする。"""
    chars = []
    for b1 in range(0xB0, 0xD0):
        for b2 in range(0xA1, 0xFF):
            try:
                ch = bytes([b1, b2]).decode("euc_jp")
            except UnicodeDecodeError:
                continue
            chars.append(ord(ch))
    return chars


COMMON = (
    list(range(0x20, 0x7F))            # ASCII
    + list(range(0xA0, 0x100))         # Latin-1
    + list(range(0x2010, 0x2028))      # 一般句読点
    + list(range(0x2030, 0x203B))
    + [0x20AC, 0x2122, 0x2190, 0x2191, 0x2192, 0x2193, 0x25A0, 0x25A1, 0x25B2, 0x25B3, 0x25BC, 0x25BD, 0x25CB, 0x25CF, 0x2605, 0x2606]
    + list(range(0x3000, 0x3040))      # 全角記号・句読点
    + list(range(0x3040, 0x30A0))      # ひらがな
    + list(range(0x30A0, 0x3100))      # カタカナ
    + list(range(0xFF01, 0xFF5F))      # 全角英数・記号
    + list(range(0xFF61, 0xFFA0))      # 半角カナ
)


def build(src: Path, dst: Path, unicodes: list[int]) -> None:
    options = subset.Options()
    options.flavor = "woff2"
    options.layout_features = ["*"]
    options.name_IDs = [0, 1, 2, 3, 4, 5, 6, 13, 14]  # ライセンス情報の名前は残す
    options.notdef_outline = True
    font = subset.load_font(str(src), options)
    subsetter = subset.Subsetter(options)
    subsetter.populate(unicodes=unicodes)
    subsetter.subset(font)
    subset.save_font(font, str(dst), options)
    print(f"{dst.name}: {dst.stat().st_size // 1024} KB")


def main() -> None:
    src_dir, out_dir = Path(sys.argv[1]), Path(sys.argv[2])
    out_dir.mkdir(parents=True, exist_ok=True)

    # ロゴ(英字のみ)
    # 可変フォントの太さを 600 に固定してからサブセット化する
    instanced = out_dir / "_CormorantGaramond-600.ttf"
    instancer.instantiateVariableFont(TTFont(str(src_dir / "CormorantGaramond[wght].ttf")), {"wght": 600}).save(str(instanced))
    build(instanced, out_dir / "CormorantGaramond-SemiBold.woff2", list(range(0x20, 0x7F)) + list(range(0xA0, 0x100)))
    instanced.unlink()
    # 見出し(明朝 Medium)
    build(src_dir / "ShipporiMincho-Medium.ttf", out_dir / "ShipporiMincho-Medium.woff2", COMMON + jis_x_0208_kanji())


if __name__ == "__main__":
    main()
