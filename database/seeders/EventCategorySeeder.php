<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('event_categories')->insert([
            [
                'name'          => 'Announcements',
                'created_by'    => 'gagah.wicaksono',
                'is_active'     => 1,
                'created_at'    => now()
            ],
            [
                'name'          => 'Sponsorship',
                'created_by'    => 'gagah.wicaksono',
                'is_active'     => 1,
                'created_at'    => now()
            ],
            [
                'name'          => 'Congratulations',
                'created_by'    => 'gagah.wicaksono',
                'is_active'     => 1,
                'created_at'    => now()
            ],
            [
                'name'          => 'Birthday',
                'created_by'    => 'gagah.wicaksono',
                'is_active'     => 1,
                'created_at'    => now()
            ],
            [
                'name'          => 'National Day',
                'created_by'    => 'gagah.wicaksono',
                'is_active'     => 1,
                'created_at'    => now()
            ],
        ]);
    }
}
