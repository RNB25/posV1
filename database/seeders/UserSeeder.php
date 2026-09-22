<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat User Admin
        User::create([
            'name' => 'Admin POS',
            'email' => 'admin@pos.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Buat User Kasir
        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@pos.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        // Tambahkan Produk Sample
        Product::create(['name' => 'Kopi Hitam', 'price' => 15000, 'stock' => 50]);
        Product::create(['name' => 'Roti Bakar', 'price' => 20000, 'stock' => 20]);
    }
}