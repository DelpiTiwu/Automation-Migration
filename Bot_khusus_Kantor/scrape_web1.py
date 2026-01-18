import json
import time
import sys
import os
import re # Tambahan buat manipulasi URL
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
import config 

# ==========================================
# 1. FUNGSI UPDATE PROGRESS
# ==========================================
def update_progress(percent, message, status="running"):
    try:
        data = {"progress": percent, "message": message, "status": status}
        path_to_public = os.path.join(os.path.dirname(os.getcwd()), 'public', 'progress.json')
        if not os.path.exists(os.path.dirname(path_to_public)): path_to_public = 'progress.json' 
        with open(path_to_public, 'w') as f: json.dump(data, f)
    except: pass

# ==========================================
# 2. SETUP DRIVER
# ==========================================
update_progress(5, "Menyiapkan Browser...", "running")
options = webdriver.ChromeOptions()
options.binary_location = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
if config.HEADLESS_MODE: options.add_argument("--headless=new")
options.add_argument("--disable-gpu"); options.add_argument("--no-sandbox"); options.add_argument("--log-level=3")

try:
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)
    wait = WebDriverWait(driver, 10)

    # ==========================================
    # 3. LOGIKA TARGET URL (YANG BARU!) 🧠
    # ==========================================
    input_url = sys.argv[1] if len(sys.argv) > 1 else "DEFAULT"
    list_target_url = []
    
    # Ambil jumlah halaman dari config (DEFAULT 1 kalau user gak set)
    jumlah_halaman = getattr(config, 'SCRAPE_PAGES_TO_SCRAPE', 1)

    if input_url != "DEFAULT" and input_url != "":
        # --- MODE MANUAL PINTAR ---
        # User tempel link, tapi kita tetep mau scrape banyak halaman
        print(f"Mode: Manual Link (Target: {jumlah_halaman} Halaman)")
        
        # 1. Cek apakah di link ada parameter 'page='?
        match = re.search(r'[?&]page=(\d+)', input_url)
        
        if match:
            start_page_manual = int(match.group(1)) # Misal user tempel page=5
        else:
            start_page_manual = 1 # Anggap page 1 kalau gak ada tulisannya
        
        # 2. Generate Link Lanjutannya
        for i in range(jumlah_halaman):
            halaman_curr = start_page_manual + i
            
            if "page=" in input_url:
                # Ganti angka page yg ada di link
                url_jadi = re.sub(r'([?&]page=)\d+', f'\g<1>{halaman_curr}', input_url)
            else:
                # Kalau belum ada page, tambahin di belakang
                separator = "&" if "?" in input_url else "?"
                url_jadi = f"{input_url}{separator}page={halaman_curr}"
            
            list_target_url.append(url_jadi)

    else:
        # --- MODE CONFIG FULL OTOMATIS ---
        # Pake settingan default config.py
        halaman_mulai = getattr(config, 'SCRAPE_START_PAGE', 1)
        print(f"Mode: Config (Mulai Hal {halaman_mulai}, Total {jumlah_halaman} Hal)")
        
        for i in range(jumlah_halaman):
            halaman_ke = halaman_mulai + i
            url_jadi = config.BASE_URL_TEMPLATE.format(halaman_ke)
            list_target_url.append(url_jadi)

    # ==========================================
    # 4. EKSEKUSI SCRAPING
    # ==========================================
    data_produk_final = []
    total_halaman = len(list_target_url)

    for i, target_url in enumerate(list_target_url):
        progress_base = int((i / total_halaman) * 100)
        update_progress(progress_base, f"Sedang memproses Halaman {i+1}...", "running")
        
        driver.get(target_url)

        # --- STEP A: AMBIL LINK (FIX URUTAN) ---
        product_links = []
        try:
            wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, "div figure a")))
            elements = driver.find_elements(By.CSS_SELECTOR, "div figure a")
            
            seen = set()
            for el in elements:
                l = el.get_attribute("href")
                if l:
                    full_link = "https://balimall.id" + l if l.startswith("/") else l
                    # LOGIC BARU: Hapus duplikat TAPI JAGA URUTAN
                    if full_link not in seen:
                        seen.add(full_link)
                        product_links.append(full_link)
        except:
            product_links = []

        if not product_links: continue

        # --- STEP B: LOOPING DETAIL ---
        total_links = len(product_links)
        for idx, link in enumerate(product_links):
            progress_step = progress_base + int((idx / total_links) * (100 / total_halaman))
            update_progress(progress_step, f"Hal {i+1}: Ambil data {idx+1}/{total_links}...", "running")
            
            driver.get(link)
            try:
                # AMBIL DATA (Sama kayak sebelumnya)
                try: nama = wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, "div.prd-info h2"))).text
                except: nama = "No Name"

                try: 
                    rp = driver.find_element(By.CSS_SELECTOR, "div.prd-info h3.price b").text
                    harga = int(rp.lower().replace("rp", "").replace(".", "").replace(",", "").strip())
                except: harga = 0

                try:
                    stok_el = driver.find_element(By.CSS_SELECTOR, "div.prd-info p.price b")
                    stok = int(stok_el.text.replace("Persediaan:", "").strip())
                except: stok = 0

                try:
                    desc_el = driver.find_element(By.CSS_SELECTOR, "div.prd-info div.text")
                    deskripsi = desc_el.get_attribute("textContent").replace("Deskripsi Produk", "").strip()
                except: deskripsi = "-"

                img_url = ""
                try:
                    img = driver.find_element(By.CSS_SELECTOR, "div.swiper-slide.swiper-slide-active img")
                    src = img.get_attribute("src")
                    img_url = "https://balimall.id" + src if src.startswith("/") else src
                except: pass
                
                data_produk_final.append({
                    "nama": nama, "harga": harga, "stok": stok,
                    "deskripsi": deskripsi, "img_url": img_url, "original_url": link
                })
            except: continue

    # SIMPAN
    with open('hasil_panen.json', 'w') as f: json.dump(data_produk_final, f, indent=4)
    update_progress(100, f"Selesai! {len(data_produk_final)} produk terambil.", "finished")
    driver.quit()

except Exception as e:
    update_progress(0, f"Error: {str(e)}", "error")
    if 'driver' in locals(): driver.quit()