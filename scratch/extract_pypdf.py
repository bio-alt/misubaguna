import pypdf
import os

pdf_path = r"C:\Users\USER\.gemini\antigravity\brain\d2753620-9721-45d7-aa8a-0e3b4f16f77d\.user_uploaded\media_1791127242369.pdf"
reader = pypdf.PdfReader(pdf_path)

out_dir = r"C:\Users\USER\Herd\misubaguna\scratch\extracted_raw"
os.makedirs(out_dir, exist_ok=True)

print(f"Total pages: {len(reader.pages)}")

count = 0
for page_num, page in enumerate(reader.pages):
    print(f"Page {page_num + 1}: {len(page.images)} images")
    for img_idx, img in enumerate(page.images):
        name = img.name
        ext = os.path.splitext(name)[1]
        out_name = f"page_{page_num + 1}_{img_idx + 1}_{name}"
        out_path = os.path.join(out_dir, out_name)
        with open(out_path, "wb") as f:
            f.write(img.data)
        print(f"  Extracted: {out_name} ({len(img.data)} bytes)")
        count += 1

print(f"Total extracted: {count}")
