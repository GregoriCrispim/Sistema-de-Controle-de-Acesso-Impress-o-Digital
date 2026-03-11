<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;

class SeedSettings extends Command
{
    protected $signature = 'settings:seed';
    protected $description = 'Seed default system settings';

    public function handle()
    {
        $defaults = [
            'canteen_start_time' => '10:00',
            'canteen_end_time' => '15:00',
            'meal_value' => '15.00',
            'manual_limit_percent' => '30',
        ];

        foreach ($defaults as $key => $value) {
            SystemSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->info('Default settings seeded successfully.');
        return Command::SUCCESS;
    }
}
