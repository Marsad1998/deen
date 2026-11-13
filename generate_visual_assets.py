import os
from PIL import Image, ImageDraw, ImageFilter

# Paths
base_dir = '/Users/marsadakbar/farzan/deen-laravel/public/images'
cat_dir = os.path.join(base_dir, 'categories')
prod_dir = os.path.join(base_dir, 'products')
hero_dir = os.path.join(base_dir, 'hero')
promo_dir = os.path.join(base_dir, 'promotions')
brand_dir = os.path.join(base_dir, 'branding')

for d in [cat_dir, prod_dir, hero_dir, promo_dir, brand_dir]:
    os.makedirs(d, exist_ok=True)

# Sources
p1 = Image.open('/Users/marsadakbar/.gemini/antigravity/brain/b24275bd-ec2e-416f-9f02-384fd367497d/appliances_commercial_plate_clean_1794512747734.jpg').convert('RGB')
p2 = Image.open('/Users/marsadakbar/.gemini/antigravity/brain/b24275bd-ec2e-416f-9f02-384fd367497d/commercial_product_showcase_1794512744568.jpg').convert('RGB')
p3 = Image.open('/Users/marsadakbar/farzan/deen-laravel/public/images/hero-appliances.jpg').convert('RGB')
p_cine = Image.open('/Users/marsadakbar/farzan/deen-laravel/public/images/hero-cinematic.jpg').convert('RGB')

def create_feathered_card(src_img, crop_box, target_size=(600, 450), pad=30, fade=25, bg_white=True):
    obj = src_img.crop(crop_box).convert('RGBA')
    obj_w, obj_h = obj.size
    
    # Create mask for object with feathered borders
    mask = Image.new('L', (obj_w, obj_h), 255)
    draw = ImageDraw.Draw(mask)
    for i in range(fade):
        alpha = int(255 * (i / max(1, fade)))
        draw.rectangle([i, i, obj_w - 1 - i, obj_h - 1 - i], outline=alpha)
    
    mask = mask.filter(ImageFilter.GaussianBlur(radius=fade/2.5))
    obj.putalpha(mask)
    
    target_w, target_h = target_size
    avail_w = target_w - (pad * 2)
    avail_h = target_h - (pad * 2)
    scale = min(avail_w / obj_w, avail_h / obj_h)
    new_w = max(1, int(obj_w * scale))
    new_h = max(1, int(obj_h * scale))
    
    obj_resized = obj.resize((new_w, new_h), Image.Resampling.LANCZOS)
    canvas = Image.new('RGBA', target_size, (255, 255, 255, 255) if bg_white else (248, 250, 252, 255))
    x = (target_w - new_w) // 2
    y = (target_h - new_h) // 2
    canvas.paste(obj_resized, (x, y), obj_resized)
    return canvas.convert('RGB')

# 1. Generate 12 Category Images (600x450 WebP & JPG)
categories_def = {
    'home-appliances':          (p3, (440, 90, 1240, 700), 20),
    'refrigerators-freezers':   (p3, (440, 170, 700, 665), 25),
    'washing-machines':         (p3, (695, 400, 890, 665), 20),
    'air-conditioners':         (p3, (870, 90, 1180, 230), 20),
    'led-tvs':                  (p3, (815, 240, 1240, 515), 20),
    'kitchen-appliances':       (p1, (150, 420, 725, 715), 20),
    'fans':                     (p1, (380, 0, 1000, 240), 20),
    'geysers-water-dispensers': (p1, (735, 240, 1200, 715), 20),
    'other-electronics':        (p2, (770, 150, 1300, 275), 20),
    'scooters':                 (p3, (880, 390, 1175, 705), 20),
    'molded-foam-multiform':    (p2, (790, 420, 1370, 720), 25),
    'furniture':                (p2, (95, 300, 730, 710), 25),
}

print("=== Generating 12 Category Images ===")
for slug, (src, box, fade) in categories_def.items():
    card = create_feathered_card(src, box, target_size=(600, 450), pad=25, fade=fade)
    webp_path = os.path.join(cat_dir, f'cat_{slug}.webp')
    jpg_path = os.path.join(cat_dir, f'cat_{slug}.jpg')
    card.save(webp_path, 'WEBP', quality=88, method=6)
    card.save(jpg_path, 'JPEG', quality=88, optimize=True)
    print(f"Generated Category: {slug} -> {os.path.getsize(webp_path)} bytes (WebP)")

# 2. Generate Product Thumbnails (600x600) and Gallery (1200x1200) for 8 Demo Showcase Products
products_def = {
    'DEMO-AC-H15T3': {
        'primary': (p3, (870, 90, 1180, 230), 20),
        'gallery_2': (p_cine, (780, 120, 1020, 230), 20),
    },
    'DEMO-REF-DW9170': {
        'primary': (p3, (440, 170, 700, 665), 25),
        'gallery_2': (p_cine, (1080, 260, 1320, 600), 20),
    },
    'DEMO-TV-TCL55': {
        'primary': (p3, (815, 240, 1240, 515), 20),
        'gallery_2': (p_cine, (740, 260, 1060, 490), 20),
    },
    'DEMO-WM-SA10': {
        'primary': (p3, (695, 400, 890, 665), 20),
        'gallery_2': (p_cine, (920, 530, 1090, 600), 15),
    },
    'DEMO-SCOOT-1200': {
        'primary': (p3, (880, 390, 1175, 705), 20),
        'gallery_2': (p_cine, (620, 470, 910, 650), 20),
    },
    'DEMO-FAN-PF56': {
        'primary': (p1, (380, 0, 1000, 240), 20),
        'gallery_2': (p1, (580, 0, 820, 240), 15),
    },
    'DEMO-KIT-ANEX4': {
        'primary': (p1, (540, 420, 725, 715), 18),
        'gallery_2': (p1, (150, 500, 525, 715), 18),
    },
    'DEMO-GEY-CANON6': {
        'primary': (p1, (735, 410, 950, 715), 18),
        'gallery_2': (p1, (985, 240, 1200, 715), 20),
    },
}

print("\n=== Generating Product Thumbnails and Gallery Assets ===")
for sku, angles in products_def.items():
    # Primary Thumbnail (600x600)
    src_prim, box_prim, fade_prim = angles['primary']
    thumb = create_feathered_card(src_prim, box_prim, target_size=(600, 600), pad=30, fade=fade_prim)
    thumb_webp = os.path.join(prod_dir, f'thumb_{sku}.webp')
    thumb_jpg = os.path.join(prod_dir, f'thumb_{sku}.jpg')
    thumb.save(thumb_webp, 'WEBP', quality=88, method=6)
    thumb.save(thumb_jpg, 'JPEG', quality=88, optimize=True)
    
    # Primary Gallery Full Size (1200x1200)
    gallery_1 = create_feathered_card(src_prim, box_prim, target_size=(1200, 1200), pad=60, fade=fade_prim*2)
    gal1_webp = os.path.join(prod_dir, f'gallery_{sku}_front.webp')
    gal1_jpg = os.path.join(prod_dir, f'gallery_{sku}_front.jpg')
    gallery_1.save(gal1_webp, 'WEBP', quality=88, method=6)
    gallery_1.save(gal1_jpg, 'JPEG', quality=88, optimize=True)

    # Gallery Angle 2 (1200x1200)
    src_sec, box_sec, fade_sec = angles['gallery_2']
    gallery_2 = create_feathered_card(src_sec, box_sec, target_size=(1200, 1200), pad=60, fade=fade_sec*2)
    gal2_webp = os.path.join(prod_dir, f'gallery_{sku}_angle.webp')
    gal2_jpg = os.path.join(prod_dir, f'gallery_{sku}_angle.jpg')
    gallery_2.save(gal2_webp, 'WEBP', quality=88, method=6)
    gallery_2.save(gal2_jpg, 'JPEG', quality=88, optimize=True)

    print(f"Product {sku} -> Thumb: {os.path.getsize(thumb_webp)} B, Gal1: {os.path.getsize(gal1_webp)} B, Gal2: {os.path.getsize(gal2_webp)} B")

# 3. Generate Promotional Retail Banner (1600x600 WebP)
print("\n=== Generating Promotional Retail Banner ===")
# Create 1600x600 banner featuring appliances on right, clean red/slate branding on left
promo_canvas = Image.new('RGB', (1600, 600), (15, 23, 42))
# Crop appliances group from p_cine and paste on right
promo_bg = p_cine.resize((1600, 893), Image.Resampling.LANCZOS)
promo_canvas.paste(promo_bg.crop((0, 140, 1600, 740)), (0, 0))

# Gradient overlay on left for readable copy
overlay = Image.new('RGBA', (1600, 600), (0, 0, 0, 0))
draw = ImageDraw.Draw(overlay)
for x in range(1600):
    if x < 700:
        alpha = 240
    elif x < 1200:
        alpha = int(240 * (1 - (x - 700) / 500))
    else:
        alpha = 0
    draw.line([(x, 0), (x, 600)], fill=(10, 10, 10, alpha))

promo_composite = Image.alpha_composite(promo_canvas.convert('RGBA'), overlay).convert('RGB')
promo_path = os.path.join(promo_dir, 'promo_showroom_cooling.webp')
promo_composite.save(promo_path, 'WEBP', quality=88, method=6)
print(f"Generated Promo Banner: {promo_path} -> {os.path.getsize(promo_path)} bytes")

# 4. Generate WebP Version of Cinematic Hero (1920x1080)
print("\n=== Optimizing Hero Background to WebP ===")
hero_webp = os.path.join(hero_dir, 'hero-cinematic.webp')
p_cine.save(hero_webp, 'WEBP', quality=85, method=6)
print(f"Hero WebP generated: {hero_webp} -> {os.path.getsize(hero_webp)} bytes")

print("\n=== All Visual Assets Generated Successfully! ===")
