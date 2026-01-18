<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapedProduct extends Model
{
    protected $fillable = [
        'nama',
        'harga',
        'stok',
        'deskripsi',
        'img_url',
        'cloud_img_url',
        'local_image_path',
        'original_url',
        'category_id',
        'subcategory_id',
        'status',
    ];

    protected $casts = [
        'harga' => 'integer',
        'stok' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }
}
