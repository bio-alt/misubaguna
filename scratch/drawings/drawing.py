"""A3 engineering drawing for expansion joint EJ-2026-6BMPAW (reportlab, vector PDF)."""
import json
import math
import sys

from reportlab.lib.colors import Color, black, white
from reportlab.lib.pagesizes import A3, landscape
from reportlab.pdfgen import canvas

MM = 72 / 25.4
SRC = sys.argv[1]
OUT = sys.argv[2]
calc = json.load(open(SRC, encoding="utf-8"))
V = calc["results"]["values"]
LINES = {l["key"]: l for l in calc["results"]["lines"]}
PSI_MPA = 0.00689476
LBF_KN = 0.00444822
LBFIN_NMM = 0.175127

# ---------------- geometry (mm) ----------------
DB, W, Q, RM, T, PLIES, N, LT = 600.0, 36.6, 25.4, 6.35, 1.5, 1, 4, 25.4
PIPE_OD, PIPE_WALL, PIPE_LEN, OVERLAP = 600.0, 9.53, 150.0, 20.0
FL_OD, FL_T, FL_BORE, BCD, HOLES, HOLE_D = 813.0, 46.0, 601.6, 749.3, 20, 35.0
BELLOWS_X0 = PIPE_LEN - OVERLAP           # bellows start measured from flange A face
BELLOWS_LEN = 2 * LT + N * Q
F2F = 2 * PIPE_LEN - 2 * OVERLAP + BELLOWS_LEN
WALL = PLIES * T
R0 = DB / 2 + WALL / 2
RC = R0 + W
BELLOWS_OD = DB + 2 * W + 2 * WALL
DM = DB + W + WALL
AE_M2 = math.pi * (DM / 1000) ** 2 / 4

c = canvas.Canvas(OUT, pagesize=landscape(A3))
PW, PH = landscape(A3)
GREY = Color(0.85, 0.85, 0.85)
BLUE = Color(0.17, 0.27, 0.62)
TEAL = Color(0.05, 0.58, 0.53)


def L(x1, y1, x2, y2, w=0.25):
    c.setLineWidth(w)
    c.line(x1 * MM, y1 * MM, x2 * MM, y2 * MM)


def text(x, y, s, size=2.5, bold=False, anchor="l", color=black):
    c.setFillColor(color)
    c.setFont("Helvetica-Bold" if bold else "Helvetica", size * MM)
    fn = {"l": c.drawString, "c": c.drawCentredString, "r": c.drawRightString}[anchor]
    fn(x * MM, y * MM, s)
    c.setFillColor(black)


def arrow(x, y, ang, size=2.2):
    a1, a2 = ang + math.radians(160), ang - math.radians(160)
    p = c.beginPath()
    p.moveTo(x * MM, y * MM)
    p.lineTo((x + size * math.cos(a1)) * MM, (y + size * math.sin(a1)) * MM)
    p.lineTo((x + size * math.cos(a2)) * MM, (y + size * math.sin(a2)) * MM)
    p.close()
    c.drawPath(p, fill=1, stroke=0)


def dim(x1, y1, x2, y2, off, label, size=2.3, along=0.5):
    """Aligned dimension between two points, offset perpendicular by `off` (sheet mm)."""
    ang = math.atan2(y2 - y1, x2 - x1)
    nx, ny = -math.sin(ang), math.cos(ang)
    a = (x1 + nx * off, y1 + ny * off)
    b = (x2 + nx * off, y2 + ny * off)
    ext = 1.5 if off >= 0 else -1.5
    L(x1, y1, a[0] + nx * ext, a[1] + ny * ext, 0.13)
    L(x2, y2, b[0] + nx * ext, b[1] + ny * ext, 0.13)
    L(a[0], a[1], b[0], b[1], 0.13)
    arrow(a[0], a[1], ang + math.pi)
    arrow(b[0], b[1], ang)
    mx, my = a[0] + (b[0] - a[0]) * along, a[1] + (b[1] - a[1]) * along
    c.saveState()
    c.translate((mx + nx * 1.2) * MM, (my + ny * 1.2) * MM)
    c.rotate(math.degrees(ang) if -90 < math.degrees(ang) <= 90 else math.degrees(ang) + 180)
    c.setFont("Helvetica", size * MM)
    c.drawCentredString(0, 0, label)
    c.restoreState()


def leader(x1, y1, x2, y2, label, size=2.3):
    L(x1, y1, x2, y2, 0.13)
    arrow(x1, y1, math.atan2(y1 - y2, x1 - x2))
    L(x2, y2, x2 + (8 if x2 >= x1 else -8), y2, 0.13)
    text(x2 + (9 if x2 >= x1 else -9), y2 - 0.8, label, size, anchor="l" if x2 >= x1 else "r")


def centerline(x1, y1, x2, y2):
    c.setDash([6, 1.5, 1, 1.5])
    L(x1, y1, x2, y2, 0.13)
    c.setDash([])


def poly(points, fill=None, stroke=0.3):
    p = c.beginPath()
    p.moveTo(points[0][0] * MM, points[0][1] * MM)
    for x, y in points[1:]:
        p.lineTo(x * MM, y * MM)
    p.close()
    c.setLineWidth(stroke)
    if fill is not None:
        c.setFillColor(fill)
    c.drawPath(p, fill=1 if fill is not None else 0, stroke=1)
    c.setFillColor(black)


def bellows_midline(samples=10):
    """Mid-wall profile as (axial, radius) points, axial from bellows start."""
    pts = [(0.0, R0), (LT, R0)]
    for k in range(N):
        y0 = LT + k * Q
        cxr, cyr = R0 + RM, y0           # first quarter arc centre (radius, axial)
        for i in range(1, samples + 1):
            a = math.pi - math.pi / 2 * i / samples   # 180 -> 90 deg in (radius, axial) frame
            pts.append((cyr + RM * math.sin(a), cxr + RM * math.cos(a)))
        ccr, ccy = RC - RM, y0 + 2 * RM   # crest semicircle centre
        for i in range(0, samples + 1):
            a = -math.pi / 2 + math.pi * i / samples
            pts.append((ccy + RM * math.sin(a), ccr + RM * math.cos(a)))
        cxr, cyr = R0 + RM, y0 + 4 * RM   # closing quarter arc centre
        for i in range(0, samples + 1):
            a = -math.pi / 2 - math.pi / 2 * i / samples
            pts.append((cyr + RM * math.sin(a), cxr + RM * math.cos(a)))
    pts += [(LT + N * Q, R0), (BELLOWS_LEN, R0)]
    return pts


def offset_polyline(pts, d):
    out = []
    for i, (x, y) in enumerate(pts):
        x0, y0 = pts[max(i - 1, 0)]
        x1, y1 = pts[min(i + 1, len(pts) - 1)]
        tx, ty = x1 - x0, y1 - y0
        n = math.hypot(tx, ty) or 1
        out.append((x + (-ty / n) * d, y + (tx / n) * d))
    return out


# ---------------- border & frame ----------------
c.setLineWidth(0.7)
c.rect(10 * MM, 10 * MM, (PW / MM - 20) * MM, (PH / MM - 20) * MM)
c.setLineWidth(0.35)
c.rect(15 * MM, 15 * MM, (PW / MM - 30) * MM, (PH / MM - 30) * MM)

# ---------------- main view: half section elevation, scale 1:6 ----------------
S = 1 / 6
AX, AY = 42.0, 192.0            # flange A face at x=AX, centreline y=AY
X = lambda a: AX + a * S         # axial -> sheet x
Yu = lambda r: AY + r * S        # radius above axis (section half)
Yl = lambda r: AY - r * S        # radius below axis (outside view half)
STEEL = Color(0.78, 0.82, 0.93)
text(AX + F2F * S / 2, 276, "ELEVATION — UPPER HALF IN SECTION  (1:6)", 3, True, "c")

# Upper half: sectioned carbon-steel parts, hatched.
for x0, x1, ri, ro in [
    (0, PIPE_LEN, PIPE_OD / 2 - PIPE_WALL, PIPE_OD / 2),
    (F2F - PIPE_LEN, F2F, PIPE_OD / 2 - PIPE_WALL, PIPE_OD / 2),
    (0, FL_T, FL_BORE / 2, FL_OD / 2),
    (F2F - FL_T, F2F, FL_BORE / 2, FL_OD / 2),
]:
    poly([(X(x0), Yu(ri)), (X(x1), Yu(ri)), (X(x1), Yu(ro)), (X(x0), Yu(ro))], fill=STEEL, stroke=0.3)
    c.saveState()
    p = c.beginPath()
    p.rect(X(x0) * MM, Yu(ri) * MM, (x1 - x0) * S * MM, (ro - ri) * S * MM)
    c.clipPath(p, stroke=0, fill=0)
    for k in range(-30, 40):
        L(X(x0) + k * 1.4, Yu(ri), X(x0) + k * 1.4 + 20, Yu(ri) + 20, 0.08)
    c.restoreState()
mid = bellows_midline()
inner = offset_polyline(mid, -WALL / 2)
outer = offset_polyline(mid, WALL / 2)
sec = [(X(BELLOWS_X0 + a), Yu(r)) for a, r in outer] + [(X(BELLOWS_X0 + a), Yu(r)) for a, r in reversed(inner)]
poly(sec, fill=black, stroke=0.15)

# Lower half: outside view.
for x0, x1, ro in [(0, FL_T, FL_OD / 2), (F2F - FL_T, F2F, FL_OD / 2),
                   (FL_T, BELLOWS_X0, PIPE_OD / 2), (BELLOWS_X0 + BELLOWS_LEN, F2F - FL_T, PIPE_OD / 2)]:
    poly([(X(x0), AY), (X(x1), AY), (X(x1), Yl(ro)), (X(x0), Yl(ro))], fill=STEEL, stroke=0.35)
sil = [(X(BELLOWS_X0 + a), Yl(r)) for a, r in outer]
poly([(sil[0][0], AY)] + sil + [(sil[-1][0], AY)], fill=Color(0.88, 0.88, 0.88), stroke=0.3)
for k in range(N):                     # crest lines seen in the outside view
    xc = X(BELLOWS_X0 + LT + k * Q + 2 * RM)
    L(xc, AY, xc, Yl(RC + WALL / 2), 0.1)
for xf in (0, F2F - FL_T):             # bolt holes (hidden)
    for r in (BCD / 2 - HOLE_D / 2, BCD / 2 + HOLE_D / 2):
        c.setDash([1.2, 0.8])
        L(X(xf), Yl(r), X(xf + FL_T), Yl(r), 0.15)
        c.setDash([])
centerline(X(-12), AY, X(F2F + 12), AY)

# Dimensions.
dim(X(0), Yu(FL_OD / 2), X(FL_T), Yu(FL_OD / 2), 5, "%.0f" % FL_T)
dim(X(BELLOWS_X0), Yu(FL_OD / 2), X(BELLOWS_X0 + BELLOWS_LEN), Yu(FL_OD / 2), 5, "%.1f BELLOWS" % BELLOWS_LEN)
dim(X(0), Yu(FL_OD / 2), X(PIPE_LEN), Yu(FL_OD / 2), 11, "%.0f" % PIPE_LEN)
dim(X(0), Yl(FL_OD / 2), X(F2F), Yl(FL_OD / 2), -8, "%.1f FACE TO FACE (FREE LENGTH)" % F2F)
dim(X(F2F), Yl(FL_OD / 2), X(F2F), Yu(FL_OD / 2), -7, "Ø%.0f FLANGE OD" % FL_OD, along=0.78)
xb = X(BELLOWS_X0 + BELLOWS_LEN)
dim(xb, Yl(BELLOWS_OD / 2), xb, Yu(BELLOWS_OD / 2), -5, "Ø%.1f OD" % BELLOWS_OD, along=0.25)
xi = X(BELLOWS_X0 + LT * 0.5)
dim(xi, Yl(DB / 2), xi, Yu(DB / 2), 0, "Ø%.0f ID" % DB, along=0.72)
# Item balloons.
def balloon(n_, bx, by, tx, ty):
    L(bx, by, tx, ty, 0.13)
    c.circle(tx * MM, ty * MM, 0.5 * MM, stroke=0, fill=1)
    c.setFillColor(white)
    c.circle(bx * MM, by * MM, 2.6 * MM, stroke=1, fill=1)
    c.setFillColor(black)
    text(bx, by - 0.9, n_, 2.6, True, "c")
balloon("3", AX - 10, Yu(380), X(12), Yu(380))
balloon("2", X(95), AY + 22, X(95), Yu(PIPE_OD / 2 - PIPE_WALL))
balloon("1", X(BELLOWS_X0 + 70), AY + 34, X(BELLOWS_X0 + 70), Yu(R0 - WALL / 2))
# Detail A marker on the first crest.
dcx, dcy = X(BELLOWS_X0 + LT + 2 * RM), Yu(RC)
c.setLineWidth(0.18)
c.circle(dcx * MM, dcy * MM, 4.5 * MM, stroke=1, fill=0)
text(dcx - 7.5, dcy + 2.5, "A", 3, True)

# ---------------- end view, scale 1:6 ----------------
EX, EY = 205.0, 192.0
text(EX, 276, "VIEW ON FLANGE FACE  (1:6)", 3, True, "c")
c.setFillColor(STEEL)
c.setLineWidth(0.35)
c.circle(EX * MM, EY * MM, FL_OD / 2 * S * MM, stroke=1, fill=1)
c.setFillColor(white)
c.circle(EX * MM, EY * MM, FL_BORE / 2 * S * MM, stroke=1, fill=1)
c.setLineWidth(0.25)
c.circle(EX * MM, EY * MM, (PIPE_OD / 2 - PIPE_WALL) * S * MM, stroke=1, fill=0)
for i in range(HOLES):
    a = 2 * math.pi * (i + 0.5) / HOLES
    hx, hy = EX + BCD / 2 * S * math.cos(a), EY + BCD / 2 * S * math.sin(a)
    c.circle(hx * MM, hy * MM, HOLE_D / 2 * S * MM, stroke=1, fill=1)
c.setFillColor(black)
c.setDash([6, 1.5, 1, 1.5])
c.setLineWidth(0.13)
c.circle(EX * MM, EY * MM, BCD / 2 * S * MM, stroke=1, fill=0)
c.setDash([])
centerline(EX - 75, EY, EX + 75, EY)
centerline(EX, EY - 75, EX, EY + 75)
for i, s_ in enumerate([
    "FLANGE Ø%.0f OD × %.0f THK, SLIP-ON BORE Ø%.1f" % (FL_OD, FL_T, FL_BORE),
    "%d HOLES Ø%.0f EQ. SP. ON Ø%.1f PCD, STRADDLING CENTRELINES" % (HOLES, HOLE_D, BCD),
    "DRILLING TO ASME B16.5 CLASS 150 NPS 24 · BOTH ENDS IDENTICAL",
]):
    text(EX, 120 - i * 3.8, s_, 2.2, anchor="c")

# ---------------- detail A, scale 2:1 ----------------
DS = 2.0
DX0, DY0 = 322.0, 178.0
text(362, 276, "DETAIL A — CONVOLUTION  (2:1)", 3, True, "c")
seg = [(LT - 6, R0)] + [(a, r) for a, r in mid if LT <= a <= LT + Q] + [(LT + Q + 0.01, R0)]
a_min = LT - 6
dx = lambda a: DX0 + (a - a_min) * DS
dy = lambda r: DY0 + (r - R0) * DS
dsec = [(dx(a), dy(r)) for a, r in offset_polyline(seg, WALL / 2)] + \
       [(dx(a), dy(r)) for a, r in reversed(offset_polyline(seg, -WALL / 2))]
poly(dsec, fill=Color(0.55, 0.55, 0.55), stroke=0.3)
dim(dx(LT), dy(R0 - WALL / 2), dx(LT + Q), dy(R0 - WALL / 2), -7, "q = %.1f (PITCH)" % Q)
dim(dx(LT + Q + 4), dy(R0 - WALL / 2), dx(LT + Q + 4), dy(RC + WALL / 2), -6, "w = %.1f" % W)
leader(dx(LT + 2 * RM - RM * 0.707), dy(RC - RM + RM * 0.707), DX0 - 4, dy(RC) + 8, "rm = R%.2f (MEAN)" % RM)
leader(dx(LT + RM) - T, dy((R0 + RC) / 2), DX0 - 4, dy((R0 + RC) / 2) - 6, "t = %d PLY × %.1f" % (PLIES, T))
leader(dx(LT - 3), dy(R0 - WALL / 2), DX0 - 4, DY0 - 12, "TANGENT Lt = %.1f" % LT)
for i, s_ in enumerate([
    "U-36 FORMING DIE · N = %d CONVOLUTIONS" % N,
    "Dm = %.1f mm · tp = t·√(Db/Dm) = %.3f mm" % (DM, T * math.sqrt(DB / DM)),
]):
    text(362, 155 - i * 3.8, s_, 2.2, anchor="c")
# ---------------- EJMA design data table ----------------
def table(x, y_top, col_w, rows, title, row_h=4.4, header=None):
    text(x, y_top + 1.5, title, 3, True, color=TEAL)
    y = y_top
    allrows = ([header] if header else []) + rows
    for i, row in enumerate(allrows):
        y -= row_h
        if header and i == 0:
            c.setFillColor(Color(0.92, 0.95, 0.95))
            c.rect(x * MM, y * MM, sum(col_w) * MM, row_h * MM, stroke=0, fill=1)
            c.setFillColor(black)
        cx = x
        for j, cell in enumerate(row):
            text(cx + 1.2, y + 1.3, str(cell), 2.15, bold=(header and i == 0))
            cx += col_w[j]
        L(x, y, x + sum(col_w), y, 0.1)
    c.setLineWidth(0.25)
    c.rect(x * MM, y * MM, sum(col_w) * MM, (y_top - y) * MM)
    cx = x
    for w_ in col_w[:-1]:
        cx += w_
        L(cx, y, cx, y_top, 0.1)
    return y


def mpa(psi):
    return psi * PSI_MPA


def bar(psi):
    return psi * 0.0689476


ln = LINES
design_rows = [
    ["Design code", "EJMA Standards, 9th Ed. (2008) — bellows §4.13.1"],
    ["Calculation ref.", "%s (design mode), %s" % (calc["reference"], calc["created"][:10])],
    ["Design pressure / temperature", "5.0 bar(g) internal / 150 °C"],
    ["Movement / cycles", "25 mm axial compression / 1,000 cycles required"],
    ["Bellows", "Unreinforced U-profile, 1 ply × 1.5 mm, N = 4, SA-240 321"],
    ["Allowable stress Sab (150 °C)", "%.1f MPa (ASME II-D 2019, Table 1A)" % mpa(V["prop_Sab"])],
    ["Effective area Ae / pressure thrust", "%.4f m² / %.1f kN (main anchors)" % (AE_M2, V["thrust"] * LBF_KN)],
    ["Spring rate (working, overall)", "%.0f N/mm  ·  axial force at 25 mm: %.1f kN" % (V["fw"] / N * LBFIN_NMM, V["Fa"] * LBF_KN)],
    ["Equivalent movement e (per conv.)", "%.2f mm" % (V["e"] * 25.4)],
]
y = table(20, 106, [52, 78], design_rows, "EJMA DESIGN DATA", row_h=4.2)

check_rows = []
for key, label, unit in [("hoop_tangent", "Tangent hoop S1", "MPa"), ("hoop_bellows", "Bellows hoop S2", "MPa"),
                         ("meridional", "Meridional S3+S4", "MPa"), ("fatigue", "Fatigue life Nc", "cycles"),
                         ("squirm_column", "Column squirm Psc", "bar(g)"), ("squirm_inplane", "In-plane squirm Psi", "bar(g)"),
                         ("movement_ec_max", "Max. compression ec", "mm"), ("instability_axial", "Axial instability ex", "mm")]:
    l = ln[key]
    v, lim = l["value"], l["limit"]
    if unit == "MPa":
        sv, sl = "%.1f" % mpa(v), "%.1f" % mpa(lim)
    elif unit == "bar(g)":
        sv, sl = "%.2f" % bar(v), "%.2f" % bar(lim)
    elif unit == "mm":
        sv, sl = "%.2f" % (v * 25.4), "%.2f" % (lim * 25.4)
    else:
        sv, sl = "%.0f" % v, "%.0f" % lim
    op = "≥" if l.get("comparison") == "ge" else "≤"
    check_rows.append([label, l["equation"], sv, op + " " + sl, unit, l["status"].upper()])
y2 = table(20, y - 5, [36, 15, 18, 22, 14, 13], check_rows, "EJMA CHECKS — RESULT: ALL PASS", row_h=4.2,
           header=["Check", "Eq.", "Value", "Limit", "Unit", "Status"])

# ---------------- notes ----------------
TEST_BAR = 1.5 * 5.0
SQUIRM_TEST_MAX = 1.5 * bar(V["Psi"])
notes = [
    "1. DESIGN: BELLOWS DESIGNED TO EJMA STANDARDS 9TH ED. (2008) §4.12–4.13; MOVEMENTS PER §4.1–4.3. DIMENSIONS IN mm.",
    "2. BELLOWS: SA-240 TYPE 321, 1 PLY × 1.5 mm, ANNEALED. LONGITUDINAL SEAM BUTT-WELDED, FULL PENETRATION, 100% PT (EJMA §7.1.2).",
    "3. ATTACHMENT WELDS: BELLOWS TANGENT TO PIPE NIPPLE, CIRCUMFERENTIAL FILLET WELD ALL ROUND; PT AFTER WELDING.",
    "4. PIPE NIPPLES ASTM A106 GR B / A53, Ø600 × 9.53 WALL. FLANGES: SLIP-ON, CARBON STEEL A105, CLASS 150 NPS 24 BOLT PATTERN,",
    "    BORE Ø601.6 FOR Ø600 PIPE; FILLET WELDED INSIDE AND OUTSIDE.",
    "5. HYDROSTATIC TEST: %.1f bar(g) (1.5 × DESIGN). TEST PRESSURE SHALL NOT EXCEED %.1f bar(g) = 1.5 × Psi (EJMA §4.12.1.6)." % (TEST_BAR, SQUIRM_TEST_MAX),
    "    JOINT MUST BE RESTRAINED AGAINST PRESSURE THRUST DURING TEST (EJMA §7.2.1).",
    "6. PRESSURE THRUST %.0f kN AND SPRING FORCE MUST BE RESISTED BY MAIN ANCHORS. PIPE GUIDES: 1ST ≤ 4D, 2ND ≤ 14D FROM" % (V["thrust"] * LBF_KN),
    "    THE JOINT (EJMA §2.10). DO NOT USE THE JOINT TO CORRECT PIPING MISALIGNMENT.",
    "7. SHIPPING BARS (PAINTED YELLOW) TO BE FITTED FOR TRANSPORT AND REMOVED AFTER INSTALLATION, BEFORE PRESSURE TEST",
    "    (EJMA §8.2). PROTECT BELLOWS FROM WELD SPATTER, ARC STRIKES AND MECHANICAL DAMAGE (EJMA §8.5).",
    "8. FINISH: CARBON STEEL PARTS PRIMED + BLUE ENAMEL; BELLOWS NOT PAINTED. TOLERANCES PER EJMA §6.17.",
    "9. PRELIMINARY — MATERIAL DATA ASME II-D 2019; FORMING TOOLING DATA PROVISIONAL. TO BE CONFIRMED BY MGI ENGINEERING.",
]
text(160, 106 + 1.5, "GENERAL NOTES", 3, True, color=TEAL)
yy = 102
for n_ in notes:
    text(160, yy, n_, 2.0)
    yy -= 3.75

# ---------------- bill of materials ----------------
bom = [
    ["1", "Bellows, U-36, 1 × 1.5 mm, N=4, Ø600 ID", "SA-240 321", "1", "9.6"],
    ["2", "Pipe nipple Ø600 × 9.53 × 150 lg", "A106 Gr B / A53", "2", "20.8"],
    ["3", "Slip-on flange Ø813 × 46, 20 × Ø35", "A105 (Cl. 150)", "2", "77.9"],
]
table(160, 50, [8, 64, 30, 10, 14], bom, "BILL OF MATERIALS", header=["Item", "Description", "Material", "Qty", "kg ea"])
text(160, 27, "TOTAL APPROX. MASS 207 kg (EMPTY)", 2.4, True)

# ---------------- title block ----------------
TBX, TBY, TBW, TBH = 300, 15, 105, 47
c.setLineWidth(0.5)
c.rect(TBX * MM, TBY * MM, TBW * MM, TBH * MM)
text(TBX + 3, TBY + TBH - 7, "PT MISUBA GUNA INDONESIA", 4, True, color=TEAL)
text(TBX + 3, TBY + TBH - 12, "SINGLE EXPANSION JOINT — FLANGED, Ø600 BORE", 2.8, True)
text(TBX + 3, TBY + TBH - 16.5, "EJMA design ref. %s" % calc["reference"], 2.4)
for i, (k, v_) in enumerate([("DWG NO.", "MGI-EJ-2026-6BMPAW-01"), ("REV", "A"), ("SCALE", "1:6 / DETAIL 2:1"),
                              ("SHEET", "1 OF 1"), ("UNITS", "mm"), ("DATE", "2026-10-13")]):
    col, row = i % 2, i // 2
    bx, by = TBX + 3 + col * 52, TBY + 23 - row * 6.5
    text(bx, by, k, 1.9, color=Color(0.4, 0.4, 0.4))
    text(bx + 15, by, v_, 2.4, True)
L(TBX, TBY + 27, TBX + TBW, TBY + 27, 0.25)
for i, k in enumerate(["DRAWN", "CHECKED", "APPROVED"]):
    text(TBX + 3 + i * 35, TBY + 3, k + ": ________", 2.0)
c.setFillColor(Color(0.86, 0.13, 0.13))
text(TBX + TBW / 2, TBY + TBH + 3, "PRELIMINARY — FOR QUOTATION / ENGINEERING REVIEW", 2.6, True, "c", color=Color(0.35, 0.35, 0.35))
c.setFillColor(black)
text(20, 12, "Third angle projection. Generated from EJMA calculator result %s and Fusion model. Do not scale." % calc["reference"], 2.0)

c.showPage()
c.save()
print("wrote", OUT)
