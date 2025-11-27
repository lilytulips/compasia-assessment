<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('products')->insert([
            [
                'product_id' => 4450,
                'types'      => 'Smartphone',
                'brand'      => 'Apple',
                'model'      => 'iPhone SE',
                'capacity'   => '2GB/16GB',
                'quantity'   => 13,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 4768,
                'types'      => 'Smartphone',
                'brand'      => 'Apple',
                'model'      => 'iPhone SE',
                'capacity'   => '2GB/32GB',
                'quantity'   => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 4451,
                'types'      => 'Smartphone',
                'brand'      => 'Apple',
                'model'      => 'iPhone SE',
                'capacity'   => '2GB/64GB',
                'quantity'   => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 4574,
                'types'      => 'Smartphone',
                'brand'      => 'Apple',
                'model'      => 'iPhone SE',
                'capacity'   => '2GB/128GB',
                'quantity'   => 16,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_id' => 6039,
                'types'      => 'Smartphone',
                'brand'      => 'Apple',
                'model'      => 'iPhone SE (2020)',
                'capacity'   => '3GB/64GB',
                'quantity'   => 18,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
