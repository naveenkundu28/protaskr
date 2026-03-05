<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            'Shopify',
            'WordPress',
            'Codeigniter',
            'Opencart',
            'Laravel',
            'Node',
            'Mongo',
            'React Js',
            'Vue Js',
            'MySql',
            'DevOPS',
            'Figma',
            'PhotoShop',
            'Adobe XD'
        ];

        foreach ($skills as $skill) {
            Skill::create(['name' => $skill]);
        }
    }
}
