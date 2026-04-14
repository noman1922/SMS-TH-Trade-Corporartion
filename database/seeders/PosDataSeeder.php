<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Product;
use App\Models\Customer;

class PosDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Products
        Product::create(['name' => 'HP Laptop 15s', 'sku' => 'HP15S', 'base_price' => 55000, 'stock' => 10, 'description' => 'Core i5 11th Gen']);
        Product::create(['name' => 'Logitech Mouse G102', 'sku' => 'LOGIG102', 'base_price' => 1850, 'stock' => 50, 'description' => 'Gaming Mouse']);
        Product::create(['name' => 'Dell Monitor 24"', 'sku' => 'DELL24', 'base_price' => 14500, 'stock' => 15, 'description' => 'Full HD IPS Display']);
        Product::create(['name' => 'Samsung 980 SSD 500GB', 'sku' => 'SAM980', 'base_price' => 7500, 'stock' => 25, 'description' => 'NVMe Gen3']);

        // Customers
        Customer::create(['name' => 'Walk-in Customer', 'mobile' => '01700000000', 'address' => 'Dhaka']);
        Customer::create(['name' => 'Rahim Ahmed', 'mobile' => '01812345678', 'address' => 'Mirpur, Dhaka']);
        Customer::create(['name' => 'Modern Solutions', 'mobile' => '01912345678', 'address' => 'Gulshan, Dhaka']);
    }
}
