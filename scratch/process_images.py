import os
import pypdfium2 as pdfium
from PIL import Image

pdf_path = r"C:\Users\USER\.gemini\antigravity\brain\d2753620-9721-45d7-aa8a-0e3b4f16f77d\.user_uploaded\media_1791127242369.pdf"
raw_dir = r"C:\Users\USER\Herd\misubaguna\scratch\extracted_raw"
base_out = r"C:\Users\USER\Herd\misubaguna\public\images\products\filtration"

os.makedirs(base_out, exist_ok=True)

# Helper function to convert any PIL image to clean RGB WebP
def save_webp(img, path, quality=90):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    if img.mode in ('RGBA', 'LA'):
        # Check if alpha channel is used
        background = Image.new('RGB', img.size, (255, 255, 255))
        background.paste(img, mask=img.split()[-1])
        background.save(path, 'WEBP', quality=quality)
    elif img.mode == 'CMYK':
        rgb_img = img.convert('RGB')
        rgb_img.save(path, 'WEBP', quality=quality)
    elif img.mode != 'RGB':
        img.convert('RGB').save(path, 'WEBP', quality=quality)
    else:
        img.save(path, 'WEBP', quality=quality)
    print(f"Saved: {path} ({os.path.getsize(path)} bytes)")

def open_clean(raw_filename):
    p = os.path.join(raw_dir, raw_filename)
    return Image.open(p)

# Also render pages at scale=2.5 (approx 180 DPI) for cropping cleanly composited sections
pdf = pdfium.PdfDocument(pdf_path)
page_renders = {}
def get_page(idx):
    if idx not in page_renders:
        print(f"Rendering page {idx + 1}...")
        page_renders[idx] = pdf[idx].render(scale=2.5).to_pil()
    return page_renders[idx]

print("=== Processing Product Images ===")

# -------------------------------------------------------------
# 1. AF Series Scraping Self-Cleaning Filter
# -------------------------------------------------------------
af_dir = os.path.join(base_out, "scraping-filter")
# Main image: page 3 main AF assembly
save_webp(open_clean("page_3_1_X5.jpg"), os.path.join(af_dir, "af-series-main.webp"))

# Scraper & filtering elements closeups
save_webp(open_clean("page_3_6_X10.jpg"), os.path.join(af_dir, "afm-scraper-elements.webp"))
save_webp(open_clean("page_3_7_X11.jpg"), os.path.join(af_dir, "afe-scraper-elements.webp"))
save_webp(open_clean("page_3_8_X12.jp2"), os.path.join(af_dir, "afb-scraper-elements.webp"))

# Crop from page 3: the subseries lineup (AFE, AFC, AFM, AFB)
p3 = get_page(2) # 0-indexed page 3
# Subseries lineup at bottom left
w, h = p3.size
# crop bottom half of left page: (left, top, right, bottom)
subseries_crop = p3.crop((int(w * 0.04), int(h * 0.45), int(w * 0.48), int(h * 0.94)))
save_webp(subseries_crop, os.path.join(af_dir, "af-subseries-models.webp"))

# On-site photo from page 4 (sugar syrup / chemical)
save_webp(open_clean("page_4_4_X16.jpg"), os.path.join(af_dir, "af-onsite-installation.webp"))

# -------------------------------------------------------------
# 2. AR Series Automated Backwash Filter
# -------------------------------------------------------------
ar_dir = os.path.join(base_out, "backwash-filter")
save_webp(open_clean("page_5_1_X19.jpg"), os.path.join(ar_dir, "ar-series-main.webp"))
save_webp(open_clean("page_4_5_X17.jpg"), os.path.join(ar_dir, "ar-onsite-piping.webp"))
save_webp(open_clean("page_5_5_X23.jpg"), os.path.join(ar_dir, "ar-multicartridge-skid.webp"))
save_webp(open_clean("page_5_6_X24.jpg"), os.path.join(ar_dir, "ar-water-treatment.webp"))

# -------------------------------------------------------------
# 3. CFC Series High-Efficiency Sealed Candle Filter
# -------------------------------------------------------------
cfc_dir = os.path.join(base_out, "candle-filter")
save_webp(open_clean("page_6_1_X25.jpg"), os.path.join(cfc_dir, "cfc-candle-filter-main.webp"))
save_webp(open_clean("page_6_2_X26.jpg"), os.path.join(cfc_dir, "cfc-candle-elements.webp"))

# Crop CFC unit with piping from page 6
p6 = get_page(5)
w, h = p6.size
cfc_unit_crop = p6.crop((int(w * 0.04), int(h * 0.18), int(w * 0.48), int(h * 0.50)))
save_webp(cfc_unit_crop, os.path.join(cfc_dir, "cfc-filter-system.webp"))

# -------------------------------------------------------------
# 4. CFP Series High-Efficiency Seal Plate Filter (Pressure Leaf Filter)
# -------------------------------------------------------------
cfp_dir = os.path.join(base_out, "pressure-leaf-filter")
save_webp(open_clean("page_6_3_X27.jpg"), os.path.join(cfp_dir, "cfp-plate-filter-main.webp"))
save_webp(open_clean("page_6_5_X29.jp2"), os.path.join(cfp_dir, "cfp-vibrator-unit.webp"))

# Crop CFP plate filter screens and vessel from page 6
cfp_vessel_crop = p6.crop((int(w * 0.72), int(h * 0.18), int(w * 0.98), int(h * 0.52)))
save_webp(cfp_vessel_crop, os.path.join(cfp_dir, "cfp-hermetic-leaf-system.webp"))

# -------------------------------------------------------------
# 5. Membrane Filter Press
# -------------------------------------------------------------
mfp_dir = os.path.join(base_out, "membrane-filter-press")
# Crop plate elements from page 6 and high-pressure clarifying systems
save_webp(open_clean("page_6_4_X28.jpg"), os.path.join(mfp_dir, "membrane-filter-press-main.webp"))

# -------------------------------------------------------------
# 6. MIF Modular Integrated Filter
# -------------------------------------------------------------
mif_dir = os.path.join(base_out, "modular-filter")
save_webp(open_clean("page_7_1_X32.jpg"), os.path.join(mif_dir, "mif-modular-system-main.webp"))
save_webp(open_clean("page_7_5_X37.jpg"), os.path.join(mif_dir, "mif-filter-station.webp"))
save_webp(open_clean("page_7_3_X35.jpg"), os.path.join(mif_dir, "mif-screen-element.webp"))

# -------------------------------------------------------------
# 7. MS High-Intensity Magnetic Iron Remover
# -------------------------------------------------------------
ms_dir = os.path.join(base_out, "magnetic-separator")
save_webp(open_clean("page_7_2_X33.jpg"), os.path.join(ms_dir, "ms-magnetic-remover-main.webp"))
save_webp(open_clean("page_7_7_X39.jpg"), os.path.join(ms_dir, "ms-magnetic-bars-cluster.webp"))
save_webp(open_clean("page_7_8_X40.jpg"), os.path.join(ms_dir, "ms-sanitary-housing.webp"))
save_webp(open_clean("page_7_9_X41.jpg"), os.path.join(ms_dir, "ms-magnetic-cartridge.webp"))

# -------------------------------------------------------------
# 8. BT Series Industrial Bag Filter
# -------------------------------------------------------------
bt_dir = os.path.join(base_out, "bag-filter")
save_webp(open_clean("page_8_1_X42.jpg"), os.path.join(bt_dir, "bt-multibag-assembly.webp"))
save_webp(open_clean("page_8_2_X43.jpg"), os.path.join(bt_dir, "bt-series-lineup.webp"))

# Crop open lid basket from page 8 top
p8 = get_page(7)
w, h = p8.size
bt_basket_crop = p8.crop((int(w * 0.52), int(h * 0.08), int(w * 0.96), int(h * 0.32)))
save_webp(bt_basket_crop, os.path.join(bt_dir, "bt-basket-and-bag.webp"))

# -------------------------------------------------------------
# 9. ST Series Basket Strainer & Pipeline Filter
# -------------------------------------------------------------
st_dir = os.path.join(base_out, "basket-filter")
save_webp(open_clean("page_9_1_X47.jpg"), os.path.join(st_dir, "st-basket-strainer-main.webp"))
save_webp(open_clean("page_9_6_X52.jp2"), os.path.join(st_dir, "st-multi-basket-stm.webp"))
save_webp(open_clean("page_9_7_X53.jp2"), os.path.join(st_dir, "st-single-basket-sts.webp"))
save_webp(open_clean("page_9_8_X54.jp2"), os.path.join(st_dir, "st-inline-pipe-stt.webp"))

p9 = get_page(8)
w, h = p9.size
st_basket_crop = p9.crop((int(w * 0.52), int(h * 0.08), int(w * 0.96), int(h * 0.32)))
save_webp(st_basket_crop, os.path.join(st_dir, "st-perforated-mesh-basket.webp"))

# -------------------------------------------------------------
# 10. CT Series Precision Cartridge Filter Housing
# -------------------------------------------------------------
ct_dir = os.path.join(base_out, "cartridge-filter")
save_webp(open_clean("page_10_4_X59.jpg"), os.path.join(ct_dir, "ct-cartridge-housing-main.webp"))
save_webp(open_clean("page_10_3_X58.jpg"), os.path.join(ct_dir, "ct-cartridge-elements.webp"))
save_webp(open_clean("page_10_5_X75.jp2"), os.path.join(ct_dir, "ct-multicartridge-ctm.webp"))
save_webp(open_clean("page_10_6_X76.jp2"), os.path.join(ct_dir, "ct-sanitary-ctr.webp"))

# -------------------------------------------------------------
# 11. CS Centrifugal Separator & RS Rotary Filter
# -------------------------------------------------------------
cs_dir = os.path.join(base_out, "centrifugal-separator")
save_webp(open_clean("page_11_1_X79.jp2"), os.path.join(cs_dir, "cs-centrifugal-separator-main.webp"))
save_webp(open_clean("page_11_2_X81.jp2"), os.path.join(cs_dir, "rs-rotary-filter-main.webp"))
save_webp(open_clean("page_11_3_X82.jpg"), os.path.join(cs_dir, "cs-separator-onsite.webp"))

# -------------------------------------------------------------
# 12. Filter Cartridges & Bags Consumables
# -------------------------------------------------------------
cons_dir = os.path.join(base_out, "consumables")
p12 = get_page(11)
w, h = p12.size
# Top left 6 cartridge elements: Pleated, Melt-blow, Titanium, Large flow, Wire-wound, Sintered
cartridges_crop = p12.crop((int(w * 0.04), int(h * 0.30), int(w * 0.48), int(h * 0.70)))
save_webp(cartridges_crop, os.path.join(cons_dir, "filter-cartridges-types.webp"))

# Top right 6 bag elements: Absolute, PP, Collar extension, SS collar, Nylon, PE
bags_crop = p12.crop((int(w * 0.52), int(h * 0.25), int(w * 0.96), int(h * 0.70)))
save_webp(bags_crop, os.path.join(cons_dir, "filter-bags-types.webp"))

# Individual cartridge elements
save_webp(open_clean("page_12_1_X84.jpg"), os.path.join(cons_dir, "pleated-cartridge.webp"))
save_webp(open_clean("page_12_2_X85.jpg"), os.path.join(cons_dir, "melt-blown-cartridge.webp"))
save_webp(open_clean("page_12_3_X86.jpg"), os.path.join(cons_dir, "titanium-rod-cartridge.webp"))
save_webp(open_clean("page_12_4_X87.jpg"), os.path.join(cons_dir, "large-flow-cartridge.webp"))
save_webp(open_clean("page_12_5_X88.jpg"), os.path.join(cons_dir, "wire-wound-cartridge.webp"))
save_webp(open_clean("page_12_6_X89.jpg"), os.path.join(cons_dir, "sintered-metal-cartridge.webp"))

# Individual bag elements
save_webp(open_clean("page_12_7_X90.jpg"), os.path.join(cons_dir, "ss-collar-filter-bag.webp"))
save_webp(open_clean("page_12_8_X91.jpg"), os.path.join(cons_dir, "nylon-mesh-filter-bag.webp"))
save_webp(open_clean("page_12_10_X93.jpg"), os.path.join(cons_dir, "absolute-liquid-filter-bag.webp"))

# -------------------------------------------------------------
# 13. Principal Branding & Facilities (Shanghai JCI Technology)
# -------------------------------------------------------------
princ_dir = os.path.join(base_out, "principal")
save_webp(open_clean("page_2_1_X3.jpg"), os.path.join(princ_dir, "jci-manufacturing-workshop.webp"))
save_webp(open_clean("page_2_2_X4.jpg"), os.path.join(princ_dir, "jci-global-operations.webp"))
save_webp(open_clean("page_1_1_X0.jpg"), os.path.join(princ_dir, "jci-engineering-facility.webp"))

print("=== All Images Successfully Extracted and Processed! ===")
