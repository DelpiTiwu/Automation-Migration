<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title> Migrasi Mart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-light: #f1f5f9;
            --card-bg: #ffffff;
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --info-gradient: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            --success-gradient: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body { 
            background-color: var(--bg-light); 
            background-image: radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%), 
                              radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.08) 0px, transparent 50%);
            color: var(--text-main); 
            font-family: 'Inter', sans-serif; 
            min-height: 100vh;
            padding-bottom: 8rem;
        }
        
        h1, h2, h3, h4, h5, h6 { color: var(--text-main); }
        p, label, .form-label { color: var(--text-muted); }
        .text-muted { color: var(--text-muted) !important; }

        .card { 
            background: var(--card-bg); 
            border: 1px solid rgba(0, 0, 0, 0.08); 
            border-radius: 20px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .card-active { 
            border: 2px solid #6366f1; 
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.15); 
        }
        .card-disabled { opacity: 0.5; pointer-events: none; filter: grayscale(0.5); }

        h1 {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800; 
            letter-spacing: -0.02em;
        }

        .form-control { 
            background-color: #f8fafc; 
            border: 2px solid #e2e8f0; 
            color: var(--text-main) !important; 
            padding: 14px 18px; 
            border-radius: 12px; 
            font-size: 1rem;
        }

        .form-control:focus { 
            background-color: #fff; 
            border-color: #818cf8; 
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); 
        }

        .form-control::placeholder { color: #94a3b8; }

        .btn { 
            border: none; 
            border-radius: 12px; 
            padding: 14px 32px; 
            font-weight: 600; 
            color: white; 
            width: 100%; 
            transition: all 0.3s; 
        }
        
        .btn-primary { background: var(--primary-gradient); }
        .btn-info { background: var(--info-gradient); }
        .btn-success { background: var(--success-gradient); }
        .btn-warning { background: var(--warning-gradient); color: white !important; }
        .btn-done { background: rgba(34, 197, 94, 0.15); color: #16a34a; border: 2px solid #22c55e; cursor: default; }

        .btn:hover:not(:disabled):not(.btn-done) { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }

        .progress { background-color: #e2e8f0; height: 1.25rem; border-radius: 100px; margin-top: 12px; }
        .progress-bar { border-radius: 100px; transition: width 0.6s ease; }

        /* STATS CARDS */
        #statsRow .card {
            border: none;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        #statsRow .card .small { color: var(--text-muted); font-weight: 500; }
        #statsRow .card .fs-4 { color: var(--text-main); }

        /* LIST BOX PREVIEW */   
        .scraped-list-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            max-height: 250px;
            overflow-y: auto;
            padding: 10px 15px;
        }
        .preview-item { 
            border-bottom: 1px solid #e2e8f0; 
            padding: 8px 0; 
            color: var(--text-main); 
            display: flex; 
            align-items: center; 
        }
        .preview-item:last-child { border-bottom: none; }
        footer { 
            color: var(--text-muted); 
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(241, 245, 249, 0.95);
            backdrop-filter: blur(8px);
            border-top: 1px solid #e2e8f0;
            padding: 16px 0 20px 0;
        }
        footer strong { color: #a855f7; }

        /* SweetAlert Custom */
        div:where(.swal2-container) div:where(.swal2-popup) { background: #fff !important; color: var(--text-main); }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="text-center mb-4">
        <h1> PANEL KENDALI </h1>
        <p class="text-muted">Pipeline: Scraping > Download > Upload > Inventory</p>
    </div>
    
    <div id="step1" class="card p-4 shadow-lg card-active">
        <h4 class="mb-3">1️⃣ FASE PANEN (SCRAPING)</h4>
        <div class="mb-3">
            <label class="form-label text-muted small">Link Target</label>
            <input type="text" id="urlInput" class="form-control" placeholder="Masukkan Link Produk Balimall">
        </div>
        
        <button class="btn btn-primary" onclick="startScraping()" id="btnScrape">MULAI SCRAPING ⚡</button>

        <div id="progressScrapeBox" style="display:none;" class="mt-3">
            <div class="d-flex justify-content-between px-2">
                <small id="statusScrape" class="text-info">Menyiapkan bot...</small>
                <small id="percentScrape" class="fw-bold">0%</small>
            </div>
            <div class="progress">
                <div id="barScrape" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%"></div>
            </div>
        </div>

        <div id="previewArea" class="mt-4" style="display:none;">
            <h6 class="text-muted border-bottom pb-2 border-secondary">
                📋 HASIL PANEN: <span id="totalProduk" class="text-primary fw-bold">0</span> PRODUK
            </h6>
            <div class="scraped-list-box">
                <ul id="previewList" class="list-unstyled mb-0"></ul>
            </div>
        </div>
    </div>

    <div id="step2" class="card p-4 shadow-lg card-disabled" style="display:none;">
        <h4 class="mb-3">2️⃣ FASE LOGISTIK (DOWNLOAD GAMBAR)</h4>
        <p class="text-muted small mb-3">Mengunduh gambar produk ke server lokal.</p>
        
        <button class="btn btn-info" onclick="startDownload()" id="btnDownload">⬇️ DOWNLOAD SEMUA GAMBAR</button>

        <div id="progressDownloadBox" style="display:none;" class="mt-3">
            <div class="d-flex justify-content-between px-2">
                <small id="statusDownload" class="text-info">Menyiapkan...</small>
                <small id="percentDownload" class="fw-bold">0%</small>
            </div>
            <div class="progress">
                <div id="barDownload" class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <div id="step3" class="card p-4 shadow-lg card-disabled" style="display:none;">
        <h4 class="mb-3">3️⃣ FASE EKSEKUSI (UPLOAD PRODUK)</h4>
        <p class="text-muted small mb-3">Login ke admin panel dan upload produk (Data + Gambar).</p>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label text-muted small">Kategori Tujuan</label>
                <select id="categorySelect" class="form-control" onchange="loadSubcategories()">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" data-name="{{ $cat->name }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small">Sub Kategori</label>
                <select id="subcategorySelect" class="form-control" disabled>
                    <option value="">-- Pilih Kategori Dulu --</option>
                </select>
            </div>
        </div>
        
        <button class="btn btn-success" onclick="startUpload()" id="btnUpload">📤 UPLOAD KE KANTORQU</button>

        <div id="progressUploadBox" style="display:none;" class="mt-3">
            <div class="d-flex justify-content-between px-2">
                <small id="statusUpload" class="text-warning">Menunggu login...</small>
                <small id="percentUpload" class="fw-bold">0%</small>
            </div>
            <div class="progress">
                <div id="barUpload" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <div id="step4" class="card p-4 shadow-lg card-disabled" style="display:none;">
        <h4 class="mb-3">4️⃣ FASE FINAL (DOWNLOAD EXCEL STOK)</h4>
        <p class="text-muted small mb-3">Generate file Excel berisi daftar produk dan stok yang rapi.</p>
        
        <button class="btn btn-warning" onclick="startInventory()" id="btnInventory">📥 DOWNLOAD EXCEL STOK</button>

        <div id="progressInventoryBox" style="display:none;" class="mt-3">
            <div class="d-flex justify-content-between px-2">
                <small id="statusInventory" class="text-warning">Menyiapkan Excel...</small>
                <small id="percentInventory" class="fw-bold">0%</small>
            </div>
            <div class="progress">
                <div id="barInventory" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 0%"></div>
            </div>
        </div>
    </div>

</div>

<!-- FOOTER -->
<footer class="text-center py-4 mt-5" style="border-top: 1px solid rgba(255,255,255,0.1);">
    <p class="text-muted mb-1" style="font-size: 0.85rem;">
        <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 600;">Bot Migrasi</span> 
        v2.0 — Automation Pipeline for KantorQu
    </p>
    <a class="text-muted small mb-0">
        ⚡ Dev by <a href="https://github.com/DelpiTiwu" target="_blank" style="color: #a855f7; text-decoration: none; font-weight: 600;">Delpiero</a> — 2026
    </p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // --- CATEGORY FUNCTIONS ---
    function loadSubcategories() {
        const catSelect = document.getElementById('categorySelect');
        const subSelect = document.getElementById('subcategorySelect');
        const catId = catSelect.value;
        
        if (!catId) {
            subSelect.innerHTML = '<option value="">-- Pilih Kategori Dulu --</option>';
            subSelect.disabled = true;
            return;
        }
        
        // Store selected category name for later use
        const catName = catSelect.options[catSelect.selectedIndex].dataset.name;
        localStorage.setItem('selectedCategory', catName);
        
        axios.get(`/api/subcategories/${catId}`).then(res => {
            const subs = res.data;
            subSelect.innerHTML = '<option value="">-- Pilih Sub Kategori --</option>';
            subs.forEach(sub => {
                subSelect.innerHTML += `<option value="${sub.id}" data-name="${sub.name}">${sub.name}</option>`;
            });
            subSelect.disabled = false;
        });
    }
    
    // Store subcategory when changed
    document.getElementById('subcategorySelect')?.addEventListener('change', function() {
        const subName = this.options[this.selectedIndex].dataset.name || '';
        localStorage.setItem('selectedSubcategory', subName);
    });

    // --- STEP 1: SCRAPING ---
    let intervalScrape;
    function startScraping() {
        const url = document.getElementById('urlInput').value;
        const btn = document.getElementById('btnScrape');
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> SEDANG MEMANEN...';
        document.getElementById('progressScrapeBox').style.display = 'block';

        axios.post("{{ route('scraping.start') }}", { url_target: url })
            .then(() => { intervalScrape = setInterval(checkScrape, 1000); })
            .catch(err => showError("Gagal Start Scrape", err));
    }

    function checkScrape() {
        axios.get("{{ route('scraping.progress') }}").then(res => {
            const data = res.data;
            updateUI('barScrape', 'statusScrape', 'percentScrape', data);

            if (data.status === 'finished' || data.progress >= 100) {
                clearInterval(intervalScrape);
                
                const btn = document.getElementById('btnScrape');
                btn.innerHTML = '✅ DATA BERHASIL DI-SCRAPING';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-done'); 

                loadPreviewData();
                activeNextStep('step2'); // Buka Step 2
            }
        });
    }

    // --- STEP 2: DOWNLOAD ---
    let intervalDownload;
    function startDownload() {
        const btn = document.getElementById('btnDownload');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> SEDANG DOWNLOAD...';
        document.getElementById('progressDownloadBox').style.display = 'block';

        axios.post("{{ route('download.start') }}")
            .then(() => { intervalDownload = setInterval(checkDownload, 1000); })
            .catch(err => showError("Gagal Start Download", err));
    }

    function checkDownload() {
        axios.get("{{ route('download.progress') }}").then(res => {
            const data = res.data;
            updateUI('barDownload', 'statusDownload', 'percentDownload', data);

            if (data.status === 'finished' || data.progress >= 100) {
                clearInterval(intervalDownload);
                
                const btn = document.getElementById('btnDownload');
                btn.innerHTML = '✅ GAMBAR BERHASIL DI-DOWNLOAD';
                btn.classList.add('btn-done');

                showSuccess("Download Selesai!", "Semua gambar berhasil diunduh. Lanjut upload produk.");
                activeNextStep('step3'); // Buka Step 3
            }
        });
    }

    // --- STEP 3: UPLOAD ---
    let intervalUpload;
    function startUpload() {
        const catSelect = document.getElementById('categorySelect');
        const subSelect = document.getElementById('subcategorySelect');
        const btn = document.getElementById('btnUpload');
        
        // Validasi kategori harus dipilih
        if (!catSelect.value) {
            Swal.fire({icon: 'warning', title: 'Pilih Kategori!', text: 'Silakan pilih kategori tujuan upload.', background: '#1e293b', color: '#fff'});
            return;
        }
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> SEDANG UPLOAD...';
        document.getElementById('progressUploadBox').style.display = 'block';

        axios.post("{{ route('upload.start') }}", {
            category_id: catSelect.value,
            subcategory_id: subSelect.value,
            category_name: catSelect.options[catSelect.selectedIndex].dataset.name,
            subcategory_name: subSelect.value ? subSelect.options[subSelect.selectedIndex].dataset.name : null
        })
            .then(() => { intervalUpload = setInterval(checkUpload, 1000); })
            .catch(err => showError("Gagal Start Upload", err));
    }

    function checkUpload() {
        axios.get("{{ route('upload.progress') }}").then(res => {
            const data = res.data;
            updateUI('barUpload', 'statusUpload', 'percentUpload', data);

            if (data.status === 'finished' || data.progress >= 100) {
                clearInterval(intervalUpload);
                
                const btn = document.getElementById('btnUpload');
                btn.innerHTML = '✅ UPLOAD BERHASIL';
                btn.classList.add('btn-done');

                showSuccess("Upload Selesai!", "Data produk masuk. Lanjut update stok.");
                activeNextStep('step4'); // Buka Step 4 (Inventory)
            }
        });
    }

    // --- STEP 4: INVENTORY ---
    let intervalInventory;
    function startInventory() {
        const btn = document.getElementById('btnInventory');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> UPDATE STOK...';
        document.getElementById('progressInventoryBox').style.display = 'block';

        axios.post("{{ route('inventory.start') }}")
            .then(() => { intervalInventory = setInterval(checkInventory, 1000); })
            .catch(err => showError("Gagal Start Inventory", err));
    }

    function checkInventory() {
        axios.get("{{ route('inventory.progress') }}").then(res => {
            const data = res.data;
            updateUI('barInventory', 'statusInventory', 'percentInventory', data);

            if (data.status === 'finished' || data.progress >= 100) {
                clearInterval(intervalInventory);
                
                const btn = document.getElementById('btnInventory');
                btn.innerHTML = '📥 KLIK UNTUK DOWNLOAD EXCEL';
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-success');
                btn.disabled = false;
                btn.onclick = function() {
                    window.location.href = "/Daftar_Stok_Produk.csv";
                    Swal.fire({
                        icon: 'success',
                        title: 'Download Dimulai!',
                        text: 'File Excel berisi ' + data.message.match(/\d+/) + ' produk telah siap.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        background: '#1e293b',
                        color: '#fff'
                    });
                };

                Swal.fire({
                    icon: 'success',
                    title: '✅ Excel Siap Download!',
                    html: `<p style="margin-top: 10px;">File <strong>Daftar_Stok_Produk.csv</strong> berhasil dibuat.</p>
                           <p style="margin-top: 5px; font-size: 0.9em; color: #94a3b8;">Berisi: ${data.message.match(/\d+/)?.[0] || '0'} produk dengan stok tersedia</p>`,
                    confirmButtonText: '📥 Download Sekarang',
                    background: '#1e293b',
                    color: '#fff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "/Daftar_Stok_Produk.csv";
                    }
                });
            }
        });
    }
    // --- HELPERS ---
    function updateUI(barId, textId, percentId, data) {
        document.getElementById(barId).style.width = data.progress + "%";
        document.getElementById(percentId).innerText = data.progress + "%";
        document.getElementById(textId).innerText = data.message;
    }

    function activeNextStep(stepId) {
        const prevStep = document.querySelector('.card-active');
        if(prevStep) {
            prevStep.classList.remove('card-active');
            prevStep.style.opacity = '0.7';
        }
        const step = document.getElementById(stepId);
        step.style.display = 'block';
        setTimeout(() => { 
            step.classList.remove('card-disabled'); 
            step.classList.add('card-active');
            step.scrollIntoView({ behavior: 'smooth' });
        }, 300);
    }

    function loadPreviewData() {
        axios.get("{{ route('scraping.result') }}").then(res => {
            const produk = res.data;
            const list = document.getElementById('previewList');
            if(list) {
                list.innerHTML = "";
                document.getElementById('totalProduk').innerText = produk.length;
                
                document.getElementById('statScraped').innerText = produk.length;
                
                const imagesReady = produk.filter(p => p.local_image_path || p.cloud_img_url).length;
                document.getElementById('statDownloaded').innerText = imagesReady;
                
                const cloudReady = produk.filter(p => p.cloud_img_url).length;
                document.getElementById('statCloud').innerText = cloudReady > 0 ? cloudReady : '-';
                
                produk.forEach(item => {
                    const hargaFmt = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(item.harga);
                    list.innerHTML += `<li class="preview-item">📦 ${item.nama} <span class="text-success ms-auto small fw-bold">${hargaFmt}</span></li>`;
                });
                document.getElementById('previewArea').style.display = 'block';
            }
        });
    }

    function showSuccess(title, text) {
        Swal.fire({
            icon: 'success', title: title, text: text,
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
            background: '#1e293b', color: '#fff'
        });
    }

    function showError(title, err) {
        Swal.fire({icon: 'error', title: title, text: err.message || err, background: '#1e293b', color: '#fff'});
    }
</script>

</body>
</html>