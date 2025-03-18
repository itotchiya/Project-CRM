<?php

namespace Database\Seeders;

use App\Models\ProjectCategory;
use Illuminate\Database\Seeder;

class ProjectCategorySeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $categories = ['Laravel', 'Vuejs', 'React', 'Zend', 'CakePhp'];

        $data = array_map(function ($item) use ($companyId) {
            return [
                'category_name' => $item,
                'company_id' => $companyId
            ];
        }, $categories);

        ProjectCategory::insert($data);
    }

}
