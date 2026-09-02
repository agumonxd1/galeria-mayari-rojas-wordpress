from pathlib import Path
from PIL import Image, ImageDraw, ImageFont, ImageOps
import sys

source = Path(sys.argv[1])
destination = Path(sys.argv[2])
destination.mkdir(parents=True, exist_ok=True)

files = sorted(
    [p for p in source.iterdir() if p.suffix.lower() in {".jpg", ".jpeg", ".png", ".webp"}],
    key=lambda p: p.name.lower(),
)

thumb_w, thumb_h, label_h = 360, 240, 54
cols, rows = 4, 3
font = ImageFont.load_default(size=15)

for sheet_index in range(0, len(files), cols * rows):
    group = files[sheet_index : sheet_index + cols * rows]
    sheet = Image.new("RGB", (cols * thumb_w, rows * (thumb_h + label_h)), "#f2eee6")
    draw = ImageDraw.Draw(sheet)
    for index, path in enumerate(group):
        row, col = divmod(index, cols)
        x, y = col * thumb_w, row * (thumb_h + label_h)
        with Image.open(path) as image:
            image = ImageOps.exif_transpose(image).convert("RGB")
            fitted = ImageOps.fit(image, (thumb_w - 12, thumb_h - 12), method=Image.Resampling.LANCZOS)
        sheet.paste(fitted, (x + 6, y + 6))
        label = path.name if len(path.name) <= 43 else path.name[:40] + "..."
        draw.text((x + 8, y + thumb_h + 8), label, fill="#171512", font=font)
    output = destination / f"contact-{sheet_index // (cols * rows) + 1:02d}.jpg"
    sheet.save(output, quality=90, optimize=True)
    print(output)
