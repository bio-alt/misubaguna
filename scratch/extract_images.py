import fitz
import os

pdf_path = r"C:\Users\USER\.gemini\antigravity\brain\d2753620-9721-45d7-aa8a-0e3b4f16f77d\.user_uploaded\media_1791127242369.pdf"
doc = fitz.open(pdf_path)

out_dir = r"C:\Users\USER\Herd\misubaguna\scratch\raw_images"
os.makedirs(out_dir, exist_ok=True)

print(f"Total Pages: {len(doc)}")

for page_idx in range(len(doc)):
    page = doc[page_idx]
    image_list = page.get_images(full=True)
    print(f"--- Page {page_idx + 1} ({len(image_list)} images) ---")
    for img_idx, img in enumerate(image_list):
        xref = img[0]
        base_image = doc.extract_image(xref)
        image_bytes = base_image["image"]
        image_ext = base_image["ext"]
        w = base_image["width"]
        h = base_image["height"]
        
        filename = f"p{page_idx + 1}_img{img_idx + 1}_{w}x{h}.{image_ext}"
        filepath = os.path.join(out_dir, filename)
        with open(filepath, "wb") as f:
            f.write(image_bytes)
        print(f"Saved: {filename} (w={w}, h={h}, ext={image_ext})")
