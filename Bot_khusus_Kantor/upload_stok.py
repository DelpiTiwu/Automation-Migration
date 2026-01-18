import json
import time
import os
import sys
import csv

# --- SETUP PATH ---
current_dir = os.path.dirname(os.path.abspath(__file__))
sys.path.append(current_dir)
os.chdir(current_dir)

def update_progress(percent, message, status="running"):
    try:
        data = {"progress": percent, "message": message, "status": status}
        project_root = os.path.dirname(current_dir)
        path_to_public = os.path.join(project_root, 'public', 'progress_inventory.json')
        with open(path_to_public, 'w') as f: json.dump(data, f)
    except: pass

# ==========================================
# MAIN PROGRAM (EXPORT EXCEL STOK)
# ==========================================
try:
    update_progress(10, "Membaca Data Produk...", "starting")
    time.sleep(0.5)

    # 1. BACA DATA JSON
    if not os.path.exists('hasil_panen_ready.json'):
        update_progress(0, "File JSON tidak ditemukan!", "error")
        sys.exit()

    with open('hasil_panen_ready.json', 'r', encoding='utf-8') as f: 
        data_produk = json.load(f)

    # Filter stok > 0
    data_stok = [p for p in data_produk if p.get('stok', 0) > 0]
    total = len(data_stok)

    if total == 0:
        update_progress(0, "Tidak ada produk dengan stok!", "error")
        sys.exit()

    update_progress(30, f"Menyiapkan {total} Produk untuk Excel...", "running")
    time.sleep(0.5)

    # 2. SETUP FILE CSV (OUTPUT) - Format Rapi untuk Excel
    project_root = os.path.dirname(current_dir)
    output_filename = "Daftar_Stok_Produk.csv"
    output_path = os.path.join(project_root, 'public', output_filename)

    update_progress(50, "Membuat File Excel...", "running")
    time.sleep(0.3)
    
    # 3. TULIS FILE CSV DENGAN FORMAT RAPI
    with open(output_path, mode='w', newline='', encoding='utf-8-sig') as file:
        writer = csv.writer(file, delimiter=';')
        
        # === HEADER EXCEL (RAPI & JELAS) ===
        writer.writerow(['NO', 'NAMA PRODUK', 'STOK', 'HARGA'])
        
        # === DATA PRODUK ===
        for i, item in enumerate(data_stok, start=1):
            # Progress bar update
            pct = 50 + int((i/total) * 45)
            nama_preview = item['nama'][:30] + '...' if len(item['nama']) > 30 else item['nama']
            update_progress(pct, f"Menulis: {nama_preview}", "running")
            time.sleep(0.03)
            
            # Format harga tanpa desimal (Rupiah)
            harga = int(item.get('harga', 0))
            stok = int(item.get('stok', 0))
            
            writer.writerow([
                i,                    # No urut
                item['nama'],         # Nama Produk (Full)
                stok,                 # Jumlah Stok
                f"Rp {harga:,}".replace(',', '.')  # Format Rupiah: Rp 50.000
            ])

    # 4. SELESAI
    update_progress(100, f"✅ File Excel Siap! ({total} Produk)", "finished")

except Exception as e:
    update_progress(0, f"Error: {str(e)}", "error")
    import traceback
    print(traceback.format_exc())