<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        Badge::updateOrCreate(
            ['slug' => 'it_room_responsible'],
            [
                'name' => 'IT Room Responsible',
                'type' => 'assignment',
                'description' => 'Responsible for the IT Room assets of a specific plant.',
            ]
        );

        Badge::updateOrCreate(
            ['slug' => 'maintenance_management'],
            [
                'name' => 'Maintenance Management',
                'type' => 'permission',
                'description' => 'Allows the user to modify maintenance-related dates.',
            ]
        );

        Badge::updateOrCreate(
            ['slug' => 'global_logs'],
            [
                'name' => 'Global Logs',
                'type' => 'permission',
                'description' => 'Allows the user to view global application records.',
            ]
        );
    }
}