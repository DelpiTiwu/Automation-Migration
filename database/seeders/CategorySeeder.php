<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Kategori dan Subkategori dari KantorQu Admin Panel
     */
    public function run(): void
    {
        $categories = [
            'Perangkat Keras' => [
                'Komputer Desktop',
                'Laptop',
                'Monitor',
                'Printer',
                'Scanner',
                'Keyboard & Mouse',
                'Storage Device',
                'Networking',
                'Aksesoris Komputer',
            ],
            'Perangkat Lunak' => [
                'Perangkat Lunak1',
                'Sistem Operasi',
                'Aplikasi Office',
                'Antivirus',
                'Software Design',
            ],
            'Alat Tulis Kantor' => [
                'Kertas',
                'Pulpen & Pensil',
                'Buku & Notes',
                'Stapler & Pembolong',
                'Map & Folder',
                'Amplop',
                'Stempel',
                'Lem & Selotip',
            ],
            'Furnitur Kantor' => [
                'Meja Kantor',
                'Kursi Kantor',
                'Lemari Arsip',
                'Rak Buku',
                'Partisi',
            ],
            'Elektronik Kantor' => [
                'Telepon',
                'Mesin Fax',
                'Proyektor',
                'CCTV',
                'UPS',
                'AC',
            ],
            'Perlengkapan Upakara' => [
                'Dupa',
                'Canang',
                'Bunga',
                'Sesajen',
                'Pelengkap Upakara',
            ],
            'Kebersihan' => [
                'Pembersih Lantai',
                'Alat Pel',
                'Sapu & Pengki',
                'Tempat Sampah',
                'Tissue & Tisu Basah',
            ],
            'Makanan & Minuman' => [
                'Kopi & Teh',
                'Snack',
                'Air Mineral',
                'Gula & Krimer',
            ],
        ];

        foreach ($categories as $categoryName => $subcategories) {
            $category = Category::create(['name' => $categoryName]);
            
            foreach ($subcategories as $subcategoryName) {
                Subcategory::create([
                    'category_id' => $category->id,
                    'name' => $subcategoryName,
                ]);
            }
        }
    }
}
