<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {

        $count = config('app.seed_record_count');
        Product::factory()->count((int)$count)
            ->make()
            ->each(function (Product $product) use ($companyId) {
                $product->company_id = $companyId;
                $product->save();
            });
    }

}
