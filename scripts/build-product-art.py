"""Kapla 상품 일러스트(12종)를 코드로 그려 seed/inbox/<SKU>.png 로 저장한다.

AI 사진 생성 모델 없이 만든 임시 비주얼이다. 실제 사진/AI 이미지가 생기면
같은 파일명으로 seed/inbox 에 넣고 prepare-images.py 를 실행하면 교체된다.
빛은 왼쪽에서 오는 석양빛, 배경은 크림, 글자와 로고는 넣지 않는다.
"""
import io
import random
import sys
from pathlib import Path

import numpy as np
import resvg_py
from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "seed" / "inbox"
W, H = 800, 1000
BASE_Y = 790  # 물건이 놓이는 바닥선
SCALE = {"KP-DIFF-SALAR": 1.1, "KP-DIFF-CEDAR": 1.1, "KP-GIFT-TRIAL": 1.12, "KP-GIFT-SET": 1.1,
         "KP-CANDLE-SALAR": 1.35, "KP-CANDLE-DUSK": 1.3}  # 그 외 1.5


# ---------- 공통 부품 ----------

def lin(id_, stops, x1=0, y1=0, x2=1, y2=0):
    s = "".join(f'<stop offset="{o}" stop-color="{c}" stop-opacity="{a}"/>' for o, c, a in stops)
    return f'<linearGradient id="{id_}" x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}">{s}</linearGradient>'


def rad(id_, stops):
    s = "".join(f'<stop offset="{o}" stop-color="{c}" stop-opacity="{a}"/>' for o, c, a in stops)
    return f'<radialGradient id="{id_}" cx="0.5" cy="0.5" r="0.5">{s}</radialGradient>'


def shadow(cx, rx, y=BASE_Y, ry=22, opacity=0.38):
    return (f'<ellipse cx="{cx + 26}" cy="{y + 6}" rx="{rx * 1.15}" ry="{ry}" fill="#2B211B" '
            f'opacity="{opacity}" filter="url(#blur12)"/>'
            f'<ellipse cx="{cx + 6}" cy="{y + 2}" rx="{rx * 0.9}" ry="{ry * 0.45}" fill="#2B211B" '
            f'opacity="{opacity + 0.15}" filter="url(#blur4)"/>')


def scene(name, glow, wall_top, wall_bottom, floor_top, floor_bottom, body, defs_extra=""):
    """배경(벽+바닥+왼쪽 석양빛)과 물건을 감싼 SVG."""
    return f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {W} {H}">
<defs>
{lin("wall", [(0, wall_top, 1), (1, wall_bottom, 1)], 0, 0, 0, 1)}
{lin("floor", [(0, floor_top, 1), (1, floor_bottom, 1)], 0, 0, 0, 1)}
{rad("glow", [(0, glow, 0.85), (0.55, glow, 0.28), (1, glow, 0)])}
{lin("sidelight", [(0, "#FFF3DC", 0.55), (0.6, "#FFF3DC", 0.0)], 0, 0, 1, 0)}
<filter id="blur4" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="4"/></filter>
<filter id="blur12" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="12"/></filter>
<filter id="blur30" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="30"/></filter>
{defs_extra}
</defs>
<rect width="{W}" height="{H}" fill="url(#wall)"/>
<ellipse cx="170" cy="470" rx="560" ry="520" fill="url(#glow)"/>
<rect y="{BASE_Y - 70}" width="{W}" height="{H - BASE_Y + 70}" fill="url(#floor)"/>
<rect y="{BASE_Y - 70}" width="{W}" height="3" fill="#FFF3DC" opacity="0.35"/>
<rect width="{W}" height="{H}" fill="url(#sidelight)"/>
<g transform="translate(400 {BASE_Y}) scale({SCALE.get(name, 1.5)}) translate(-400 {-BASE_Y})">
{body}
</g>
</svg>'''


def amber_glass_id(prefix):
    return (lin(f"{prefix}A", [(0, "#E6A857", 1), (0.35, "#B87326", 1), (0.75, "#7C4614", 1), (1, "#4E2A0C", 1)])
            + lin(f"{prefix}Cap", [(0, "#4A3A31", 1), (0.5, "#2B211B", 1), (1, "#17100C", 1)]))


def rollon(cx, base=BASE_Y, w=150, h=250, scale=1.0, tag=True, glass="A"):
    """오일 퍼퓸(롤온) 병. 갈색 유리, 어두운 캡, 빈 종이 태그."""
    w, h = w * scale, h * scale
    x = cx - w / 2
    top = base - h
    neck_w = w * 0.5
    cap_w = w * 0.66
    cap_h = 100 * scale
    g = f'''<g>
<rect x="{x}" y="{top}" width="{w}" height="{h}" rx="{24 * scale}" fill="url(#rb{glass}A)"/>
<rect x="{x + w * 0.12}" y="{top + h * 0.18}" width="{w * 0.76}" height="{h * 0.72}" rx="{14 * scale}" fill="#3A1F08" opacity="0.28"/>
<rect x="{x + 10 * scale}" y="{top + 18 * scale}" width="{14 * scale}" height="{h - 44 * scale}" rx="{7 * scale}" fill="#FFF3DC" opacity="0.5"/>
<rect x="{x + w - 22 * scale}" y="{top + 24 * scale}" width="{6 * scale}" height="{h - 60 * scale}" rx="{3 * scale}" fill="#FFD9A0" opacity="0.22"/>
<rect x="{cx - neck_w / 2}" y="{top - 34 * scale}" width="{neck_w}" height="{40 * scale}" fill="url(#rb{glass}A)"/>
<rect x="{cx - cap_w / 2}" y="{top - 34 * scale - cap_h}" width="{cap_w}" height="{cap_h + 6 * scale}" rx="{12 * scale}" fill="url(#rb{glass}Cap)"/>
<rect x="{cx - cap_w / 2 + 8 * scale}" y="{top - 30 * scale - cap_h}" width="{7 * scale}" height="{cap_h - 8 * scale}" rx="3" fill="#FFF3DC" opacity="0.22"/>
</g>'''
    if tag:
        tx, ty = cx + neck_w / 2 - 4 * scale, top - 22 * scale
        g += f'''<path d="M{tx} {ty} C {tx + 30 * scale} {ty + 10 * scale}, {tx + 46 * scale} {ty + 30 * scale}, {tx + 48 * scale} {ty + 52 * scale}" fill="none" stroke="#6B5A4D" stroke-width="{2.2 * scale}"/>
<g transform="rotate(-8 {tx + 48 * scale} {ty + 56 * scale})"><rect x="{tx + 28 * scale}" y="{ty + 52 * scale}" width="{40 * scale}" height="{58 * scale}" rx="{3 * scale}" fill="#FBF8F2"/>
<rect x="{tx + 28 * scale}" y="{ty + 52 * scale}" width="{40 * scale}" height="{58 * scale}" rx="{3 * scale}" fill="none" stroke="#D9CBB5" stroke-width="1.2"/>
<circle cx="{tx + 48 * scale}" cy="{ty + 62 * scale}" r="{3 * scale}" fill="#D9CBB5"/></g>'''
    return g


def tube(cx, color_a, color_b, base=BASE_Y, w=150, h=330):
    """핸드 퍼퓸 알루미늄 튜브(캡을 아래로 세운 형태)."""
    x = cx - w / 2
    top = base - h
    return f'''<g>
<rect x="{x + 10}" y="{base - 62}" width="{w - 20}" height="62" rx="10" fill="url(#tubeCap)"/>
<path d="M{x} {base - 56} L{x + 6} {top + 16} Q{x + 8} {top + 4} {x + 20} {top + 4} L{x + w - 20} {top + 4} Q{x + w - 8} {top + 4} {x + w - 6} {top + 16} L{x + w} {base - 56} Z" fill="url(#tubeBody)"/>
<rect x="{x + 8}" y="{top - 6}" width="{w - 16}" height="20" rx="3" fill="#2B211B" opacity="0.14"/>
<rect x="{x + 8}" y="{top - 6}" width="{w - 16}" height="20" rx="3" fill="url(#tubeBody)" opacity="0.9"/>
{"".join(f'<line x1="{x + 16}" y1="{top + 2 + i * 3.4}" x2="{x + w - 16}" y2="{top + 2 + i * 3.4}" stroke="#2B211B" stroke-opacity="0.18" stroke-width="1"/>' for i in range(4))}
<rect x="{x + 12}" y="{top + 30}" width="14" height="{h - 110}" rx="7" fill="#FFF3DC" opacity="0.55"/>
<rect x="{x + w - 24}" y="{top + 40}" width="7" height="{h - 130}" rx="3" fill="#2B211B" opacity="0.1"/>
</g>''', lin("tubeBody", [(0, color_a, 1), (0.55, color_b, 1), (1, color_b, 1)]) + \
        lin("tubeCap", [(0, "#4A3A31", 1), (0.5, "#2B211B", 1), (1, "#17100C", 1)])


def diffuser(cx, glass_a, glass_b, base=BASE_Y, w=210, h=270, reeds=True, reed_color="#B98C5A"):
    x = cx - w / 2
    top = base - h
    neck_w = w * 0.34
    r = ""
    if reeds:
        random.seed(7)
        for i in range(11):
            dx = (i - 5) * 22 + random.randint(-6, 6)
            r += (f'<line x1="{cx + (i - 5) * 3}" y1="{top - 70}" x2="{cx + dx * 0.9}" y2="{top - 330 - random.randint(0, 50)}" '
                  f'stroke="{reed_color}" stroke-width="5" stroke-linecap="round"/>')
    return f'''<g>
{r}
<path d="M{x + 14} {top + 40} Q{x} {top + 40} {x} {top + 70} L{x} {base - 18} Q{x} {base} {x + 18} {base} L{x + w - 18} {base} Q{x + w} {base} {x + w} {base - 18} L{x + w} {top + 70} Q{x + w} {top + 40} {x + w - 14} {top + 40} L{cx + neck_w / 2} {top + 40} L{cx + neck_w / 2} {top} L{cx - neck_w / 2} {top} L{cx - neck_w / 2} {top + 40} Z" fill="url(#dGlass)"/>
<rect x="{x + 14}" y="{top + 90}" width="{w - 28}" height="{h - 130}" rx="14" fill="{glass_b}" opacity="0.35"/>
<rect x="{x + 14}" y="{top + 56}" width="14" height="{h - 80}" rx="7" fill="#FFFFFF" opacity="0.55"/>
<rect x="{x + w - 26}" y="{top + 64}" width="7" height="{h - 100}" rx="3" fill="#FFFFFF" opacity="0.18"/>
<rect x="{cx - neck_w / 2 - 4}" y="{top - 10}" width="{neck_w + 8}" height="14" rx="4" fill="#6B5A4D" opacity="0.65"/>
<rect x="{x + w * 0.22}" y="{top + h * 0.42}" width="{w * 0.56}" height="{h * 0.3}" rx="6" fill="#FBF8F2" opacity="0.92"/>
</g>''', lin("dGlass", [(0, glass_a, 0.95), (0.55, glass_b, 0.95), (1, glass_b, 1)])


def jar(cx, ceramic_a, ceramic_b, base=BASE_Y, w=300, h=250, lid=True, lit=False, label=True):
    x = cx - w / 2
    top = base - h
    g = f'''<g>
<path d="M{x} {top + 24} Q{x} {top} {x + 26} {top} L{x + w - 26} {top} Q{x + w} {top} {x + w} {top + 24} L{x + w} {base - 30} Q{x + w} {base} {x + w - 30} {base} L{x + 30} {base} Q{x} {base} {x} {base - 30} Z" fill="url(#jarBody)"/>
<rect x="{x + 16}" y="{top + 30}" width="14" height="{h - 80}" rx="7" fill="#FFF3DC" opacity="0.6"/>
<rect x="{x + w - 30}" y="{top + 34}" width="8" height="{h - 90}" rx="4" fill="#2B211B" opacity="0.1"/>
'''
    if label:
        g += f'<rect x="{x + w * 0.26}" y="{top + h * 0.34}" width="{w * 0.48}" height="{h * 0.34}" rx="5" fill="#FBF8F2" opacity="0.94"/>'
    if lid:
        g += (f'<rect x="{x + 10}" y="{top - 26}" width="{w - 20}" height="34" rx="9" fill="url(#lidG)"/>'
              f'<rect x="{x + 22}" y="{top - 22}" width="12" height="20" rx="5" fill="#FFF3DC" opacity="0.25"/>')
    else:
        g += f'<ellipse cx="{cx}" cy="{top + 4}" rx="{w / 2 - 14}" ry="9" fill="#F7E7C7" opacity="0.9"/>'
        g += f'<line x1="{cx}" y1="{top + 4}" x2="{cx}" y2="{top - 20}" stroke="#2B211B" stroke-width="3"/>'
        if lit:
            g += f'''<ellipse cx="{cx}" cy="{top - 52}" rx="150" ry="170" fill="#FFB35A" opacity="0.28" filter="url(#blur30)"/>
<path d="M{cx} {top - 74} C {cx + 20} {top - 50}, {cx + 14} {top - 24}, {cx} {top - 18} C {cx - 14} {top - 24}, {cx - 20} {top - 50}, {cx} {top - 74} Z" fill="#FFC56E"/>
<path d="M{cx} {top - 58} C {cx + 9} {top - 44}, {cx + 6} {top - 30}, {cx} {top - 26} C {cx - 6} {top - 30}, {cx - 9} {top - 44}, {cx} {top - 58} Z" fill="#FFF3DC"/>'''
    g += "</g>"
    return g, lin("jarBody", [(0, ceramic_a, 1), (0.6, ceramic_b, 1), (1, ceramic_b, 1)]) + \
        lin("lidG", [(0, "#4A3A31", 1), (0.5, "#2B211B", 1), (1, "#17100C", 1)])


def salt(cx, spread, count, base=BASE_Y, seed=3):
    random.seed(seed)
    out = ""
    for _ in range(count):
        x = cx + random.uniform(-spread, spread)
        y = base + random.uniform(-8, 46)
        s = random.uniform(5, 15)
        pts = [(x, y - s), (x + s * 0.9, y - s * 0.2), (x + s * 0.6, y + s * 0.7), (x - s * 0.7, y + s * 0.6), (x - s, y - s * 0.3)]
        p = " ".join(f"{a:.1f},{b:.1f}" for a, b in pts)
        out += f'<polygon points="{p}" fill="#FFFFFF" opacity="{random.uniform(0.75, 0.97):.2f}" stroke="#D5DCE4" stroke-width="0.8"/>'
    return out


def salt_bed(cx, w):
    return (f'<ellipse cx="{cx}" cy="{BASE_Y + 14}" rx="{w}" ry="40" fill="#F4F6F9"/>'
            f'<ellipse cx="{cx - 20}" cy="{BASE_Y + 8}" rx="{w * 0.8}" ry="26" fill="#FFFFFF" opacity="0.9"/>')


def cedar(x0, y0, length=280, angle=-20, needles=26, seed=11, flip=1):
    random.seed(seed)
    import math
    a = math.radians(angle)
    x1, y1 = x0 + math.cos(a) * length * flip, y0 + math.sin(a) * length
    out = f'<path d="M{x0} {y0} L{x1} {y1}" stroke="#5C4630" stroke-width="5" stroke-linecap="round"/>'
    for i in range(needles):
        t = 0.1 + 0.9 * i / needles
        px, py = x0 + (x1 - x0) * t, y0 + (y1 - y0) * t
        for side in (-1, 1):
            la = a + side * math.radians(55 + random.randint(-10, 10))
            ln = 60 * (1.15 - t * 0.6) + random.randint(0, 10)
            out += (f'<path d="M{px:.1f} {py:.1f} L{px + math.cos(la) * ln * flip:.1f} {py + math.sin(la) * ln:.1f}" '
                    f'stroke="{random.choice(["#4F6B3F", "#5F7A4A", "#3F5A33"])}" stroke-width="3.4" stroke-linecap="round"/>')
    return out


def moss(cx, cy, w=170):
    random.seed(5)
    out = ""
    for _ in range(46):
        out += (f'<circle cx="{cx + random.uniform(-w, w):.0f}" cy="{cy + random.uniform(-14, 18):.0f}" '
                f'r="{random.uniform(8, 17):.0f}" fill="{random.choice(["#5E7A47", "#6F8B52", "#4A6638", "#7C9660"])}"/>')
    return out


def stone(cx, w=360):
    return (f'<ellipse cx="{cx}" cy="{BASE_Y + 20}" rx="{w}" ry="34" fill="#C9B79B"/>'
            f'<ellipse cx="{cx - 30}" cy="{BASE_Y + 12}" rx="{w * 0.85}" ry="22" fill="#D9C8AC"/>')


# ---------- SKU별 장면 ----------

def make(sku):
    rb = amber_glass_id("rbA")
    cx = 400
    if sku == "KP-OIL-SALAR":
        body = salt_bed(cx, 280) + shadow(cx, 90) + rollon(cx) + salt(cx, 260, 60)
        return scene(sku, "#DDE6F2", "#EEF0F2", "#F4EEE4", "#E9E4DE", "#D8D0C6", body, rb)
    if sku == "KP-OIL-DUSK":
        body = stone(cx) + shadow(cx, 90) + rollon(cx, glass="A")
        return scene(sku, "#F2A260", "#EFD9C0", "#F4EEE4", "#D9C3A0", "#B99D78", body, rb)
    if sku == "KP-OIL-CEDAR":
        body = (cedar(cx + 70, BASE_Y + 22, 330, -12, seed=2) + moss(cx - 190, BASE_Y + 20)
                + shadow(cx - 40, 90) + rollon(cx - 40))
        return scene(sku, "#CFD8B5", "#E6E6D2", "#F1ECDF", "#D7D0BC", "#BDB59B", body, rb)
    if sku == "KP-HAND-SALAR":
        t, d = tube(cx, "#FFFFFF", "#E2E6EA")
        body = salt_bed(cx, 280) + shadow(cx, 82) + t + salt(cx, 260, 55, seed=9)
        return scene(sku, "#DDE6F2", "#EEF0F2", "#F4EEE4", "#E9E4DE", "#D8D0C6", body, d)
    if sku == "KP-HAND-DUSK":
        t, d = tube(cx, "#D9A07C", "#B07650")
        body = stone(cx) + shadow(cx, 82) + t
        return scene(sku, "#F2A260", "#EFD9C0", "#F4EEE4", "#D9C3A0", "#B99D78", body, d)
    if sku == "KP-DIFF-SALAR":
        g, d = diffuser(cx, "#F4F8FB", "#D3DEE8")
        body = salt_bed(cx, 280) + shadow(cx, 110) + g + salt(cx, 260, 40, seed=4)
        return scene(sku, "#E3EAF3", "#F1F2F2", "#F6F1E8", "#EAE5DE", "#D9D1C7", body, d)
    if sku == "KP-DIFF-CEDAR":
        g, d = diffuser(cx - 50, "#4F6B4A", "#233A27", reed_color="#C9A472")
        body = (cedar(cx + 120, BASE_Y + 22, 300, -10, seed=8) + shadow(cx - 50, 110) + g)
        return scene(sku, "#CFD8B5", "#E6E6D2", "#F1ECDF", "#D7D0BC", "#BDB59B", body, d)
    if sku == "KP-CAR-DUSK":
        # 어두운 가죽 위의 작은 테라코타 병 + 나무 캡 + 통풍구 클립
        body = f'''<rect y="{BASE_Y - 70}" width="{W}" height="{H}" fill="#2A1F1A"/>
<ellipse cx="400" cy="{BASE_Y + 40}" rx="340" ry="30" fill="#3A2C25" opacity="0.7"/>
{shadow(400, 100)}
<g>
<rect x="320" y="{BASE_Y - 330}" width="160" height="22" rx="6" fill="url(#clip)"/>
<rect x="344" y="{BASE_Y - 310}" width="12" height="60" fill="#8A5636"/>
<rect x="444" y="{BASE_Y - 310}" width="12" height="60" fill="#8A5636"/>
<rect x="332" y="{BASE_Y - 340}" width="136" height="22" rx="8" fill="url(#wood)"/>
<rect x="322" y="{BASE_Y - 250}" width="156" height="250" rx="34" fill="url(#terra)"/>
<rect x="336" y="{BASE_Y - 232}" width="14" height="190" rx="7" fill="#FFE8CC" opacity="0.5"/>
<rect x="458" y="{BASE_Y - 226}" width="7" height="170" rx="3" fill="#2B211B" opacity="0.18"/>
<rect x="352" y="{BASE_Y - 170}" width="96" height="60" rx="5" fill="#FBF8F2" opacity="0.92"/>
</g>'''
        d = (lin("clip", [(0, "#4A3A31", 1), (1, "#17100C", 1)]) + lin("wood", [(0, "#D9AE7A", 1), (0.5, "#B98351", 1), (1, "#8C5C34", 1)])
             + lin("terra", [(0, "#D9916A", 1), (0.5, "#B05F3C", 1), (1, "#7B3E26", 1)]))
        return scene(sku, "#F2A260", "#3A2C25", "#2A1F1A", "#2A1F1A", "#17100C", body, d)
    if sku == "KP-CANDLE-SALAR":
        j, d = jar(cx, "#FBF4E8", "#E2D6C2")
        body = salt_bed(cx, 300) + shadow(cx, 150) + j + salt(cx, 270, 55, seed=12)
        return scene(sku, "#E3EAF3", "#F1F2F2", "#F6F1E8", "#EAE5DE", "#D9D1C7", body, d)
    if sku == "KP-CANDLE-DUSK":
        j, d = jar(cx, "#D18B66", "#98512F", lid=False, lit=True)
        body = stone(cx, 380) + shadow(cx, 150) + j
        return scene(sku, "#F2A260", "#6B3F2E", "#4A2B21", "#7A5640", "#5A3C2C", body, d)
    if sku == "KP-GIFT-TRIAL":
        # 크림색 선물 상자 위에 3ml 미니 롤온 3개
        minis = "".join(rollon(cx + dx, base=BASE_Y - 70, w=96, h=150, scale=1, tag=True) for dx in (-130, 0, 130))
        body = f'''{shadow(cx, 280)}
<rect x="{cx - 290}" y="{BASE_Y - 70}" width="580" height="90" rx="6" fill="url(#box)"/>
<rect x="{cx - 290}" y="{BASE_Y - 70}" width="580" height="16" fill="#FFFFFF" opacity="0.5"/>
<rect x="{cx - 8}" y="{BASE_Y - 70}" width="16" height="90" fill="#B8956A" opacity="0.85"/>
{minis}'''
        d = lin("box", [(0, "#FBF6EC", 1), (1, "#E6DAC4", 1)], 0, 0, 0, 1)
        return scene(sku, "#F2C088", "#EFE3D0", "#F4EEE4", "#E3D5BC", "#CDBB9B", body, rb + d)
    if sku == "KP-GIFT-SET":
        j, d = jar(cx + 105, "#FBF4E8", "#E2D6C2", base=BASE_Y - 70, w=190, h=170, label=False)
        body = f'''{shadow(cx, 300)}
<rect x="{cx - 290}" y="{BASE_Y - 250}" width="580" height="260" rx="8" fill="#9C7A55"/>
<rect x="{cx - 290}" y="{BASE_Y - 250}" width="580" height="30" fill="#7E5F40"/>
{rollon(cx - 105, base=BASE_Y - 70, w=120, h=200, scale=1)}
{j}
<rect x="{cx - 300}" y="{BASE_Y - 130}" width="600" height="148" rx="8" fill="url(#giftbox)"/>
<rect x="{cx - 300}" y="{BASE_Y - 130}" width="600" height="14" fill="#FFFFFF" opacity="0.55"/>
<rect x="{cx - 300}" y="{BASE_Y - 130}" width="14" height="148" fill="#FFF3DC" opacity="0.5"/>
<rect x="{cx - 300}" y="{BASE_Y - 62}" width="600" height="18" fill="#A8845A"/>
<rect x="{cx - 300}" y="{BASE_Y - 62}" width="600" height="4" fill="#FFFFFF" opacity="0.25"/>
<path d="M{cx} {BASE_Y - 53} C {cx - 60} {BASE_Y - 120}, {cx - 82} {BASE_Y - 70}, {cx - 12} {BASE_Y - 52} Z" fill="#B8956A"/>
<path d="M{cx} {BASE_Y - 53} C {cx + 60} {BASE_Y - 120}, {cx + 82} {BASE_Y - 70}, {cx + 12} {BASE_Y - 52} Z" fill="#B8956A"/>
<circle cx="{cx}" cy="{BASE_Y - 53}" r="11" fill="#A8845A"/>'''
        dd = lin("giftbox", [(0, "#FBF6EC", 1), (1, "#E3D6BE", 1)], 0, 0, 0, 1)
        return scene(sku, "#F2C088", "#EFE3D0", "#F4EEE4", "#E3D5BC", "#CDBB9B", body, rb + d + dd)
    raise KeyError(sku)


SKUS = ["KP-OIL-SALAR", "KP-OIL-DUSK", "KP-OIL-CEDAR", "KP-HAND-SALAR", "KP-HAND-DUSK",
        "KP-DIFF-SALAR", "KP-DIFF-CEDAR", "KP-CAR-DUSK", "KP-CANDLE-SALAR", "KP-CANDLE-DUSK",
        "KP-GIFT-TRIAL", "KP-GIFT-SET"]


def render(svg: str) -> Image.Image:
    png = resvg_py.svg_to_bytes(svg_string=svg, width=W * 2, height=H * 2)
    img = Image.open(io.BytesIO(png)).convert("RGB")
    # 약한 필름 입자: 그라데이션 띠(banding)를 줄이고 인쇄물 같은 질감을 준다
    arr = np.asarray(img).astype(np.float32)
    rng = np.random.default_rng(1)
    arr += rng.normal(0, 2.2, arr.shape[:2])[..., None]
    return Image.fromarray(np.clip(arr, 0, 255).astype(np.uint8))


def main() -> int:
    sys.stdout.reconfigure(encoding="utf-8")
    OUT.mkdir(parents=True, exist_ok=True)
    only = sys.argv[1:] or SKUS
    for sku in only:
        render(make(sku)).save(OUT / f"{sku}.png")
        print("saved", sku)
    return 0


if __name__ == "__main__":
    sys.exit(main())
