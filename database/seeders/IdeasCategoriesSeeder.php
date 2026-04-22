<?php

namespace Database\Seeders;

use App\Models\IdeaCategory;
use Illuminate\Database\Seeder;

class IdeasCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['web', 'mobile', 'AI', 'others', 'extension'];

        foreach ($categories as $categoryName) {
            IdeaCategory::firstOrCreate([
                'name' => $categoryName,
            ]);
        }
    }
}