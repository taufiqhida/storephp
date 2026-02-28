<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Category;
use App\Models\StoreSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        AdminUser::firstOrCreate(
            ['email' => 'admin@taufiqstore.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // Sample Admin biasa
        AdminUser::firstOrCreate(
            ['email' => 'staf@taufiqstore.com'],
            [
                'name' => 'Staf Toko',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'permissions' => ['manage_products', 'manage_orders', 'manage_testimonials'],
                'is_active' => true,
            ]
        );

        // Store Settings
        StoreSetting::firstOrCreate(
            ['id' => 1],
            [
                'store_name' => 'Taufiq Store',
                'store_description' => 'Toko online terpercaya dengan produk berkualitas',
                'store_email' => 'taufiqstore@gmail.com',
                'whatsapp_number' => '628123456789',
                'message_template' => "Halo Taufiq Store! 👋\n\nSaya ingin memesan:\n\n{items}\n\n💰 Subtotal: {subtotal}\n🏦 Biaya Admin: {admin_fee}\n🏷️ Diskon: {discount}\n🔢 Kode Unik: {unique_code}\n💵 *TOTAL: {total}*\n\n💳 Pembayaran: {payment}\n\n👤 Nama: {name}\n📱 HP: {phone}\n📋 Kode Pesanan: {order_code}\n\n📝 Catatan: {note}\n\nMohon konfirmasi pesanan saya. Terima kasih! 🙏",
                'site_mode' => 'live',
            ]
        );

        // Kategori Contoh
        $categories = [
            ['name' => 'Baju', 'slug' => 'baju', 'icon' => 'heroicon-o-shirt', 'sort_order' => 1],
            ['name' => 'Celana', 'slug' => 'celana', 'icon' => 'heroicon-o-scissors', 'sort_order' => 2],
            ['name' => 'Aksesoris', 'slug' => 'aksesoris', 'icon' => 'heroicon-o-sparkles', 'sort_order' => 3],
            ['name' => 'Tas', 'slug' => 'tas', 'icon' => 'heroicon-o-shopping-bag', 'sort_order' => 4],
            ['name' => 'Sepatu', 'slug' => 'sepatu', 'icon' => 'heroicon-o-star', 'sort_order' => 5],
        ];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        $this->command->info('✅ Seeder selesai!');
        $this->command->info('📧 Super Admin: admin@taufiqstore.com / password');
        $this->command->info('📧 Staf Admin: staf@taufiqstore.com / password');
    }
}
