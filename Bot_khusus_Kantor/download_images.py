import json
import os
import sys
import requests
from PIL import Image
import io
import concurrent.futures

# --- SETUP PATH & DEBUGGING ---
current_dir = os.path.dirname(os.path.abspath(__file__))
sys.path.append(current_dir)
os.chdir(current_dir)

# Bikin file log buat mata-mata
def log_debug(msg):
    with open(os.path.join(current_dir, "debug_log.txt"), "a") as f:
        f.write(msg + "\n")

# Hapus log lama biar bersih
if os.path.exists(os.path.join(current_dir, "debug_log.txt")):
    os.remove(os.path.join(current_dir, "debug_log.txt"))

log_debug("--- STARTING BOT FROM DASHBOARD ---")
log_debug(f"Working Directory: {os.getcwd()}")

import config 

# --- CLOUDINARY SETUP ---
try:
    from cloudinary_upload import init_cloudinary, upload_to_cloudinary, CLOUDINARY_AVAILABLE
    CLOUDINARY_ENABLED = init_cloudinary()
    if CLOUDINARY_ENABLED:
        log_debug("[CLOUD] Cloudinary ENABLED - Images will be uploaded to cloud")
    else:
        log_debug("[INFO] Cloudinary NOT configured - Using local storage only")
except ImportError:
    CLOUDINARY_AVAILABLE = False
    CLOUDINARY_ENABLED = False
    log_debug("[INFO] Cloudinary module not found - Using local storage only")

# --- KONFIGURASI ---
FOLDER_GAMBAR = "downloaded_images"
HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
}

BASE_DIR = os.path.dirname(os.path.abspath(__file__)) 
IMG_DIR_ABS = os.path.join(BASE_DIR, FOLDER_GAMBAR)
if not os.path.exists(IMG_DIR_ABS):
    os.makedirs(IMG_DIR_ABS)

# ==========================================
# 1. UPDATE PROGRESS
# ==========================================
def update_progress(percent, message, status="running"):
    try:
        data = {"progress": percent, "message": message, "status": status}
        project_root = os.path.dirname(BASE_DIR) 
        path_to_public = os.path.join(project_root, 'public', 'progress_download.json')
        
        with open(path_to_public, 'w') as f: json.dump(data, f)
    except Exception as e:
        log_debug(f"Error update progress: {e}")

# ==========================================
# 2. IMAGE PROCESSING
# ==========================================
def process_image(file_path):
    try:
        img = Image.open(file_path)
        if img.mode in ("RGBA", "P"): img = img.convert("RGB")
        width, height = img.size
        if width != height:
            max_side = max(width, height)
            new_img = Image.new("RGB", (max_side, max_side), (255, 255, 255))
            left = (max_side - width) // 2
            top = (max_side - height) // 2
            new_img.paste(img, (left, top))
            img = new_img
        
        buffer = io.BytesIO()
        img.save(buffer, format="JPEG", quality=90)
        with open(file_path, "wb") as f: f.write(buffer.getbuffer())
        return True
    except: return False

# ==========================================
# 3. WORKER
# ==========================================
def download_single_item(item_data):
    item = item_data.copy()
    url = item.get('img_url', '')
    
    if not url or not url.startswith("http"):
        item['local_image_path'] = None
        item['cloud_img_url'] = None
        return item

    idx = item.get('_index', 0)
    nama_file = f"img_{idx}.jpg"
    path_lokal = os.path.join(IMG_DIR_ABS, nama_file)
    
    try:
        resp = requests.get(url, headers=HEADERS, timeout=10)
        if resp.status_code == 200:
            with open(path_lokal, 'wb') as f: f.write(resp.content)
            process_image(path_lokal)
            item['local_image_path'] = path_lokal
            
            # Upload to Cloudinary if enabled
            if CLOUDINARY_ENABLED:
                cloud_url = upload_to_cloudinary(path_lokal, public_id=f"product_{idx}")
                item['cloud_img_url'] = cloud_url
                
                # Optionally delete local file after cloud upload to save space
                # if cloud_url:
                #     os.remove(path_lokal)
                #     item['local_image_path'] = None
            else:
                item['cloud_img_url'] = None
        else:
            item['local_image_path'] = None
            item['cloud_img_url'] = None
    except Exception as e:
        log_debug(f"Error downloading {url}: {e}")
        item['local_image_path'] = None
        item['cloud_img_url'] = None
    return item

# ==========================================
# 4. MAIN PROGRAM
# ==========================================
if __name__ == "__main__":
    update_progress(0, "Menyiapkan Download...", "starting")
    
    try:
        path_json = os.path.join(BASE_DIR, 'hasil_panen.json')
        log_debug(f"Mencari JSON di: {path_json}")

        if not os.path.exists(path_json):
            log_debug("CRITICAL: File hasil_panen.json TIDAK DITEMUKAN!")
            update_progress(0, "File JSON tidak ditemukan", "error")
            exit()

        with open(path_json, 'r') as f: data_produk = json.load(f)
        log_debug(f"JSON Ditemukan. Total item: {len(data_produk)}")

        # Inject Index
        for i, item in enumerate(data_produk): item['_index'] = i
        
        data_result = []
        done = 0
        workers = getattr(config, 'MAX_WORKERS_DOWNLOAD', 5)

        with concurrent.futures.ThreadPoolExecutor(max_workers=workers) as executor:
            futures = {executor.submit(download_single_item, i): i for i in data_produk}
            
            for future in concurrent.futures.as_completed(futures):
                res = future.result()
                if '_index' in res: del res['_index']
                data_result.append(res)
                done += 1
                pct = 5 + int((done / len(data_produk)) * 90)
                update_progress(pct, f"Download [{done}/{len(data_produk)}]...", "running")

        path_out = os.path.join(BASE_DIR, 'hasil_panen_ready.json')
        with open(path_out, 'w') as f: json.dump(data_result, f, indent=4)

        log_debug("SELESAI. JSON Output tersimpan.")
        update_progress(100, "Selesai!", "finished")

    except Exception as e:
        log_debug(f"ERROR FATAL: {e}")
        update_progress(0, f"Error: {str(e)}", "error")