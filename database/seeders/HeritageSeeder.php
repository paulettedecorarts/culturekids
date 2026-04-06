<?php

namespace Database\Seeders;

use App\Models\Tribe;
use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class HeritageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/heritage_data.json');
        
        if (!File::exists($jsonPath)) {
            $this->command->error("Could not find heritage_data.json at {$jsonPath}");
            return;
        }

        $tribes = json_decode(File::get($jsonPath), true);
        
        $this->command->info("Found " . count($tribes) . " tribes. Starting seed...");

        foreach ($tribes as $tData) {
            $tribe = Tribe::firstOrCreate(
                ['name' => $tData['name']],
                [
                    'hero_name' => $tData['hero'],
                    'hero_icon' => $tData['icon'] ?? null,
                    'hero_emoji' => $tData['flag'] ?? null,
                    'greeting' => $tData['greeting'] ?? null,
                    'region' => $tData['region'] ?? null,
                    'color' => $tData['color'] ?? null,
                ]
            );

            // Print progress
            $this->command->info("Seeded Tribe: {$tribe->name}");

            foreach ($tData['activities'] as $aData) {
                Activity::firstOrCreate(
                    [
                        'title' => $aData['title'], 
                        'tribe_id' => $tribe->id
                    ],
                    [
                        'type' => $aData['type'],
                        'age_range' => $aData['age'] ?? null,
                        'description' => $aData['desc'] ?? null,
                        'star_points' => $aData['points'] ?? 10,
                        'metadata' => [
                            'tag' => $aData['tag'] ?? null,
                            'icon' => $aData['icon'] ?? null,
                        ],
                        'is_published' => true,
                    ]
                );
            }
        }
        
        $this->command->info("Heritage seeding complete! All activities loaded.");
    }
}
