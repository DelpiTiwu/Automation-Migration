<?php

use App\Http\Controllers\ScrapingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ScrapingController::class, 'index']);

// SCRAPING
Route::post('/scraping/start', [ScrapingController::class, 'startScraping'])->name('scraping.start');
Route::get('/scraping/progress', [ScrapingController::class, 'checkScrapingProgress'])->name('scraping.progress'); 
Route::get('/scraping/result', [ScrapingController::class, 'getScrapedData'])->name('scraping.result');

// DOWNLOAD
Route::post('/download/start', [ScrapingController::class, 'startDownload'])->name('download.start');
Route::get('/download/progress', [ScrapingController::class, 'checkDownloadProgress'])->name('download.progress');

// UPLOAD
Route::post('/upload/start', [ScrapingController::class, 'startUpload'])->name('upload.start');
Route::get('/upload/progress', [ScrapingController::class, 'checkUploadProgress'])->name('upload.progress'); 

// INVENTORY
Route::post('/inventory/start', [ScrapingController::class, 'startInventory'])->name('inventory.start');
Route::get('/inventory/progress', [ScrapingController::class, 'checkInventoryProgress'])->name('inventory.progress');

// CATEGORY API
Route::get('/api/categories', [ScrapingController::class, 'getCategories'])->name('api.categories');
Route::get('/api/subcategories/{categoryId}', [ScrapingController::class, 'getSubcategories'])->name('api.subcategories');
