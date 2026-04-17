<?php

namespace Database\Seeders;

use App\Models\Requirement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requirements = [
            'developer',
            'ui_ux',
            'proposal',
            'devops',
            'proposal_with_developer'
        ];

        foreach ($requirements as $req) {
            Requirement::updateOrCreate(
                ['requirement' => $req], // avoid duplicates
                ['requirement' => $req]
            );
        }
    }
}
