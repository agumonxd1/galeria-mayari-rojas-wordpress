from pathlib import Path
from PIL import Image, ImageOps
import json
import sys

source = Path(sys.argv[1])
destination = Path(sys.argv[2])
destination.mkdir(parents=True, exist_ok=True)

selection = [
    ("IMG_1788.jpeg", "elmar-rojas-proceso-creativo-01.jpg", "Elmar Rojas trabajando sobre una obra gráfica"),
    ("IMG_1790.jpeg", "elmar-rojas-proceso-creativo-02.jpg", "Elmar Rojas interviniendo una obra gráfica"),
    ("IMG_3709.jpeg", "elmar-rojas-taller-escultura-01.jpg", "Elmar Rojas trabajando una escultura en su taller"),
    ("IMG_3710.jpeg", "elmar-rojas-taller-escultura-02.jpg", "Elmar Rojas junto a una escultura en proceso"),
    ("IMG_5189.jpg", "elmar-rojas-taller-pintura.jpg", "Elmar Rojas trabajando en una pintura"),
    ("IMG_5838.jpg", "elmar-rojas-retrato-taller-01.jpg", "Retrato de Elmar Rojas en su taller"),
    ("IMG_6588.jpg", "elmar-rojas-retrato-taller-02.jpg", "Elmar Rojas junto a una de sus pinturas"),
    ("IMG_6920.jpg", "elmar-rojas-escultura-metal-01.jpg", "Elmar Rojas junto a una escultura de metal"),
    ("IMG_6954.jpg", "elmar-rojas-escultura-metal-02.jpg", "Elmar Rojas en su taller de escultura"),
    ("IMG_6991.jpg", "elmar-rojas-escultura-metal-03.jpg", "Elmar Rojas junto a una escultura monumental"),
    ("Elmar Rojas Mexico City.jpg", "elmar-rojas-ciudad-de-mexico.jpg", "Elmar Rojas en Ciudad de México"),
    ("DSC00668.jpg", "elmar-rojas-arquitectura.jpg", "Elmar Rojas en un espacio de arquitectura contemporánea"),
    ("feria Mexico 2012  2.jpg", "elmar-rojas-feria-mexico-2012.jpg", "Elmar Rojas durante una feria de arte en México en 2012"),
    ("Exposicion Maestro Rojas en Museo Ixchel Guatemala.jpg", "elmar-rojas-museo-ixchel-guatemala.jpg", "Elmar Rojas durante una exposición en el Museo Ixchel de Guatemala"),
    ("FOTO Elmar Rojas con Luis Diaz, Robelio Mendez, Rudy Cotton, Cesar Mendez y Olga Garcia.jpg", "elmar-rojas-artistas-guatemaltecos.jpg", "Elmar Rojas junto a artistas guatemaltecos"),
    ("Escultura del Maestro Elmar Rojas _Andasolo_ en Avenida Reforma nov 2015.jpg", "elmar-rojas-andasolo-avenida-reforma-2015.jpg", "Escultura Andasolo de Elmar Rojas en Avenida Reforma, 2015"),
    ("IMG_7106.jpg", "elmar-rojas-mayari-rojas-andasolo.jpg", "Elmar Rojas y Mayarí Rojas junto a la escultura Andasolo"),
    ("IMG_8086.jpg", "elmar-rojas-exposicion.jpg", "Elmar Rojas en una exposición de arte"),
    ("IMG_8088.jpg", "elmar-rojas-retrato.jpg", "Retrato de Elmar Rojas"),
]

manifest = []
for order, (original_name, output_name, alt) in enumerate(selection, start=1):
    original = source / original_name
    if not original.exists():
        raise FileNotFoundError(original)
    with Image.open(original) as image:
        image = ImageOps.exif_transpose(image).convert("RGB")
        image.thumbnail((2400, 2400), Image.Resampling.LANCZOS)
        output = destination / output_name
        image.save(output, "JPEG", quality=85, optimize=True, progressive=True)
        manifest.append({
            "order": order,
            "original": original_name,
            "file": output_name,
            "alt": alt,
            "width": image.width,
            "height": image.height,
        })

(destination / "manifest.json").write_text(
    json.dumps(manifest, ensure_ascii=False, indent=2), encoding="utf-8"
)
print(json.dumps({"count": len(manifest), "destination": str(destination)}, ensure_ascii=False))
