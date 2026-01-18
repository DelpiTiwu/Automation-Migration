<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ScrapedProduct;
use Illuminate\Http\Request;

class ScrapingController extends Controller
{
    public function index()
    {
        $categories = Category::with('subcategories')->get();
        return view('dashboard', compact('categories'));
    }

    // =========================================================
    // CATEGORY API
    // =========================================================
    public function getCategories()
    {
        return response()->json(Category::all());
    }

    public function getSubcategories($categoryId)
    {
        return response()->json(Subcategory::where('category_id', $categoryId)->get());
    }

    // =========================================================
    // 1. SCRAPING
    // =========================================================
    public function startScraping(Request $request)
    {
        // Reset Progress
        $fileProgress = public_path('progress_scrape.json');
        file_put_contents($fileProgress, json_encode([
            'progress' => 0, 
            'message' => 'Menyiapkan mesin bot...', 
            'status' => 'starting'
        ]));

        $urlTarget = $request->input('url_target');
        if(!$urlTarget) $urlTarget = "DEFAULT"; 

        $botFolder = base_path('Bot_khusus_Kantor');
        $pythonPath = $botFolder . '\.venv\Scripts\python.exe';
        $scriptName = 'scrape_web1.py';
        
        $command = "start /B \"\" \"$pythonPath\" \"$scriptName\" \"$urlTarget\" > NUL 2>&1";
        pclose(popen("cd /d \"$botFolder\" && $command", "r"));

        return response()->json(['status' => 'started']);
    }

    public function checkScrapingProgress()
    {
        // Sesuaikan nama file ini sama yang ditulis Python scraping lu
        // Kalau python lu nulis ke "progress.json", ubah bawah ini jadi "progress.json"
        $path = public_path('progress.json'); 
        
        if (file_exists($path)) {
            clearstatcache(); 
            $data = json_decode(file_get_contents($path), true);
            return response()->json($data);
        }
        
        return response()->json(['progress' => 0, 'message' => 'Menunggu bot...', 'status' => 'waiting']);
    }

    public function getScrapedData()
    {
        $path = base_path('Bot_khusus_Kantor/hasil_panen.json');
        if (file_exists($path)) {
           clearstatcache();
           $data = json_decode(file_get_contents($path), true);
           return response()->json($data);
        }
        return response()->json([]);
    }

    // =========================================================
    // 2. DOWNLOAD
    // =========================================================
    public function startDownload()
    {
        file_put_contents(public_path('progress_download.json'), json_encode(['progress' => 0, 'message' => 'Menyiapkan...', 'status' => 'starting']));

        $botFolder = base_path('Bot_khusus_Kantor');
        $pythonPath = $botFolder . '\.venv\Scripts\python.exe';
        $scriptName = 'download_images.py'; 

        $command = "start /B \"\" \"$pythonPath\" \"$scriptName\" > NUL 2>&1";
        pclose(popen("cd /d \"$botFolder\" && $command", "r"));

        return response()->json(['status' => 'started']);
    }

    public function checkDownloadProgress()
    {
        $path = public_path('progress_download.json');
        if (file_exists($path)) {
            clearstatcache();
            return response()->json(json_decode(file_get_contents($path), true));
        }
        return response()->json(['progress' => 0, 'message' => 'Waiting...', 'status' => 'waiting']);
    }

    // =========================================================
    // 3. UPLOAD
    // =========================================================
    public function startUpload(Request $request)
    {
        $fileProgress = public_path('progress_upload.json');
        file_put_contents($fileProgress, json_encode([
            'progress' => 0, 
            'message' => 'Menyiapkan bot upload...', 
            'status' => 'starting'
        ]));

        $botFolder = base_path('Bot_khusus_Kantor'); 
        $pythonPath = $botFolder . '\.venv\Scripts\python.exe';
        $scriptName = 'upload_bot.py'; 

        // Write runtime config with category selection from dashboard
        $runtimeConfig = [
            'category_id' => $request->input('category_id'),
            'subcategory_id' => $request->input('subcategory_id'),
            'category_name' => $request->input('category_name'),
            'subcategory_name' => $request->input('subcategory_name'),
        ];
        file_put_contents($botFolder . '/runtime_config.json', json_encode($runtimeConfig, JSON_PRETTY_PRINT));

        $command = "cd /d \"$botFolder\" && start /B \"\" \"$pythonPath\" \"$scriptName\" > NUL 2>&1";
        pclose(popen($command, "r"));

        return response()->json(['status' => 'started']);
    }

    // 👇 INI FUNGSI YANG KURANG TADI, MAKANYA BAR GAK JALAN 👇
    public function checkUploadProgress()
    {
        $path = public_path('progress_upload.json');
        if (file_exists($path)) {
            clearstatcache();
            return response()->json(json_decode(file_get_contents($path), true));
        }
        return response()->json(['progress' => 0, 'message' => 'Menunggu Upload...', 'status' => 'waiting']);
    }
    public function startInventory()
    {
        // 1. Reset file progress biar barnya mulai dari 0
        $fileProgress = public_path('progress_inventory.json');
        file_put_contents($fileProgress, json_encode([
            'progress' => 0, 
            'message' => 'Menyiapkan bot inventory...', 
            'status' => 'starting'
        ]));

        // 2. Setup Path
        // Pastikan nama folder "Bot_khusus_Kantor" sesuai sama di laptop lu
        $botFolder = base_path('Bot_khusus_Kantor'); 
        $pythonPath = $botFolder . '\.venv\Scripts\python.exe';
        
    
        $scriptName = 'upload_stok.py'; 

        // 3. Eksekusi di Background (Windows Mode)
        // > NUL 2>&1 biar Laravel gak nungguin bot selesai (Asynchronous)
        $command = "cd /d \"$botFolder\" && start /B \"\" \"$pythonPath\" \"$scriptName\" > NUL 2>&1";
        pclose(popen($command, "r"));

        return response()->json(['status' => 'started']);
    }

    public function checkInventoryProgress()
    {
        // Baca file JSON yang ditulis sama Python inventory_bot.py
        $path = public_path('progress_inventory.json');
        
        if (file_exists($path)) {
            clearstatcache(); // Hapus cache biar datanya real-time
            return response()->json(json_decode(file_get_contents($path), true));
        }
        
        // Default kalau file belum kebuat
        return response()->json(['progress' => 0, 'message' => 'Menunggu Inventory...', 'status' => 'waiting']);
    }
}