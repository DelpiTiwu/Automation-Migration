import json
import time
import os
import sys
import re

# --- 1. SETUP PATH & LOGGING ---
current_dir = os.path.dirname(os.path.abspath(__file__))
sys.path.append(current_dir)
os.chdir(current_dir)

def log_debug(msg):
    try:
        with open("debug_log_upload.txt", "a", encoding="utf-8") as f:
            f.write(f"[{time.strftime('%H:%M:%S')}] {msg}\n")
    except: pass

if os.path.exists("debug_log_upload.txt"):
    try: os.remove("debug_log_upload.txt")
    except: pass

log_debug("🚀 STARTING UPLOAD BOT...")

try:
    import config
    from selenium import webdriver
    from selenium.webdriver.chrome.service import Service
    from selenium.webdriver.common.by import By
    from selenium.webdriver.common.keys import Keys # PENTING
    from selenium.webdriver.support.ui import Select
    from selenium.webdriver.support.ui import WebDriverWait
    from selenium.webdriver.support import expected_conditions as EC
    from webdriver_manager.chrome import ChromeDriverManager
except Exception as e:
    log_debug(f"CRITICAL: Gagal Import. {e}")
    sys.exit()

# ==========================================
# 2.5 LOAD RUNTIME CONFIG (FROM DASHBOARD)
# ==========================================
RUNTIME_CONFIG = {}
runtime_config_path = os.path.join(current_dir, 'runtime_config.json')
if os.path.exists(runtime_config_path):
    try:
        with open(runtime_config_path, 'r') as f:
            RUNTIME_CONFIG = json.load(f)
        log_debug(f"Runtime Config Loaded: {RUNTIME_CONFIG}")
    except: pass

# Use runtime config if available, otherwise fallback to static config
TARGET_KATEGORI = RUNTIME_CONFIG.get('category_name') or getattr(config, 'TARGET_KATEGORI', 'Perangkat Lunak')
TARGET_SUBKATEGORI = RUNTIME_CONFIG.get('subcategory_name') or getattr(config, 'TARGET_SUBKATEGORI', 'Perangkat Lunak1')
log_debug(f"Using Category: {TARGET_KATEGORI} / {TARGET_SUBKATEGORI}")

# ==========================================
# 2. UPDATE PROGRESS
# ==========================================
def update_progress(percent, message, status="running"):
    try:
        data = {"progress": percent, "message": message, "status": status}
        project_root = os.path.dirname(current_dir)
        path_to_public = os.path.join(project_root, 'public', 'progress_upload.json')
        with open(path_to_public, 'w') as f: json.dump(data, f)
    except: pass

# ==========================================
# 3. HELPER
# ==========================================
def safe_click(driver, element):
    try: element.click()
    except: driver.execute_script("arguments[0].click();", element)

def pilih_select2(driver, wait, select_id, value):
    try:
        wait.until(lambda d: not d.find_element(By.ID, select_id).get_attribute("disabled"))
        xpath_trigger = f"//select[@id='{select_id}']/following-sibling::span[contains(@class, 'select2-container')]"
        container = wait.until(EC.element_to_be_clickable((By.XPATH, xpath_trigger)))
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", container)
        safe_click(driver, container)
        
        xpath_input = "//span[contains(@class, 'select2-container--open')]//input[@class='select2-search__field']"
        inp = wait.until(EC.visibility_of_element_located((By.XPATH, xpath_input)))
        inp.clear()
        inp.send_keys(value)
        time.sleep(1)
        
        xpath_res = f"//li[contains(@class, 'select2-results__option') and contains(text(), '{value}')]"
        wait.until(EC.element_to_be_clickable((By.XPATH, xpath_res))).click()
        return True
    except: return False

# ==========================================
# 4. MAIN PROGRAM
# ==========================================
driver = None
try:
    update_progress(5, "Menyiapkan Browser...", "starting")
    
    if not os.path.exists('hasil_panen_ready.json'):
        update_progress(0, "File data tidak ditemukan", "error")
        sys.exit()

    with open('hasil_panen_ready.json', 'r') as f: data_produk = json.load(f)
    
    options = webdriver.ChromeOptions()
    options.add_argument("--ignore-certificate-errors")
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)
    driver.maximize_window()
    wait = WebDriverWait(driver, 20)

    # --- LOGIN ---
    update_progress(10, "Login...", "running")
    driver.get("https://admin.kantorqu.co.id/login")
    time.sleep(3)

    try:
        try: email_field = wait.until(EC.element_to_be_clickable((By.NAME, "email")))
        except: email_field = driver.find_element(By.CSS_SELECTOR, "input[type='email']")
        email_field.clear()
        email_field.send_keys(config.EMAIL_LOGIN)
        
        driver.find_element(By.NAME, "password").send_keys(config.PASS_LOGIN)
        
        try: driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        except: driver.find_element(By.XPATH, "//button[contains(text(), 'Login')]").click()
        
        time.sleep(5)
        if "login" in driver.current_url: raise Exception("Gagal Login")
    except Exception as e:
        raise Exception(f"Error Login: {e}")

    # --- UPLOAD ---
    URL_CREATE = "https://admin.kantorqu.co.id/product-management/product/create"
    total = len(data_produk)
    
    for i, item in enumerate(data_produk):
        pct = 15 + int(((i+1)/total) * 85)
        update_progress(pct, f"Upload [{i+1}/{total}]: {item['nama']}...", "running")
        
        for attempt in range(3):
            try:
                driver.get(URL_CREATE)
                
                # 1. KATEGORI & DATA (using runtime config from dashboard)
                pilih_select2(driver, wait, "id_category", TARGET_KATEGORI)
                time.sleep(0.5)
                pilih_select2(driver, wait, "id_subcategory", TARGET_SUBKATEGORI)

                sku_val = f"{config.PREFIX_SKU}{i+1000}{int(time.time())}"[-15:]
                driver.find_element(By.NAME, "sku").send_keys(sku_val)
                driver.find_element(By.NAME, "name").send_keys(item['nama'])
                slug = re.sub(r'[^a-z0-9\s-]', '', item['nama'].lower()).replace(" ", "-")
                driver.find_element(By.NAME, "slug").send_keys(slug)

                driver.find_element(By.NAME, "price").send_keys(str(item['harga']))
                try: driver.find_element(By.NAME, "stock").send_keys(str(item.get('stok', 10)))
                except: pass

                try:
                    desc = item.get('deskripsi', '-').replace("\n", "<br>").replace("'", "\\'").replace('"', '\\"')
                    driver.execute_script(f"$('.summernote').summernote('code', '{desc}');")
                except: pass

                driver.execute_script("window.scrollBy(0, 400);")

                # ==================================================
                # 🔥 BAGIAN INI TADI HILANG DI CODE LU 🔥
                # ==================================================

                # --- F. KEYWORD (FIX: 2 Kata, Tanpa Upakara) ---
                try:
                    words = item['nama'].split()
                    keywords_list = [w for w in words[:2] if w] # Cuma 2 kata pertama
                    
                    try:
                        driver.find_element(By.XPATH, "//label[contains(text(), 'Keyword')]/following-sibling::span").click()
                        time.sleep(0.5)
                    except: pass
                    
                    xpath_input_kw = "//span[contains(@class, 'select2-container--open')]//input[@class='select2-search__field']"
                    active_search = driver.find_element(By.XPATH, xpath_input_kw)
                    
                    for kw in keywords_list:
                        active_search.send_keys(kw)
                        time.sleep(0.1)
                        active_search.send_keys(Keys.ENTER)
                except Exception as e:
                    log_debug(f"Warning Keyword: {e}")

                driver.execute_script("window.scrollBy(0, 200);")

                # --- G. SPESIFIKASI (FIX: Isi "-") ---
                try:
                    driver.find_element(By.NAME, "spesifikasi_name[]").send_keys("-")
                    driver.find_element(By.NAME, "spesifikasi_value[]").send_keys("-")
                except: pass

                # ==================================================

                driver.execute_script("window.scrollBy(0, 400);")

                # 7. LAINNYA
                driver.find_element(By.NAME, "weight").send_keys(config.DEFAULT_BERAT)
                driver.find_element(By.NAME, "dimension_long").send_keys(config.DEFAULT_PANJANG)
                driver.find_element(By.NAME, "dimension_wide").send_keys(config.DEFAULT_LEBAR)
                driver.find_element(By.NAME, "dimension_high").send_keys(config.DEFAULT_TINGGI)
                pilih_select2(driver, wait, "id_unit", config.DEFAULT_SATUAN)

                local_path = item.get('local_image_path')
                if local_path and os.path.exists(local_path):
                    try:
                        file_input = driver.find_element(By.XPATH, "//tbody[@id='tbodyImage']//input[@type='file']")
                        file_input.send_keys(local_path)
                    except: pass
                
                driver.execute_script("window.scrollTo(0, 0);")
                time.sleep(1)
                try: Select(driver.find_element(By.NAME, "status")).select_by_index(1)
                except: pass
                
                driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
                time.sleep(4)
                break 

            except Exception as e:
                time.sleep(2)

    update_progress(100, "Semua Selesai!", "finished")
    driver.quit()

except Exception as e:
    update_progress(0, f"Error: {str(e)}", "error")
    if driver: driver.quit()