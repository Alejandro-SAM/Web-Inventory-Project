<?php

// SEEDER FOR INVENTORY TABLE TESTING, NOT TO BE USED IN PRODUCTION.
// DELETE ONCE PRODUCTION IS LIVE.

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('inventory')
            ->where('it_internal_number', 'like', 'IT-TEST-%')
            ->delete();

        $categories = ['Laptop', 'Desktop', 'Monitor', 'Printer', 'Scanner', 'Tablet'];
        $brands = ['Dell', 'HP', 'Lenovo', 'Epson', 'Zebra'];
        $departments = ['IT', 'HR', 'Finance', 'Production', 'Quality', 'Maintenance'];
        $locations = ['IT Office', 'Warehouse', 'Production Line', 'Administration', 'EHS'];
        $businessUnits = ['BU1', 'BU2', 'BU3', 'BU4', 'BU5', 'BU6'];
        $plants = ['B', 'D', 'G', 'H', 'MP', 'MPI', 'MPII'];
        $states = ['active', 'inactive', 'maintenance', 'disposed', 'lost'];
        $systems = ['Windows 10', 'Windows 11', 'Ubuntu', 'N/A'];

        $purchaseOriginCountries = [
            'Mexico',
            'United States',
            'China',
            'Germany',
            'Japan',
            'South Korea',
            'Canada',
        ];

        for ($i = 1; $i <= 2000; $i++) {
            $warrantyStartDate = Carbon::now()
                ->subDays(rand(30, 1095));

            $warrantyExpiryDate = $warrantyStartDate->copy()
                ->addYears(rand(1, 4));
            
            DB::table('inventory')->insert([
                'it_internal_number' => 'IT-TEST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'serial_number' => 'SNTEST' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'asset_number' => 'AST-' . str_pad($i, 5, '0', STR_PAD_LEFT),

                'description' => 'Test asset generated for inventory table visualization',
                'model' => 'Model-' . rand(100, 999),
                'brand' => $brands[array_rand($brands)],
                'category' => $categories[array_rand($categories)],

                'warranty_start_date' => $warrantyStartDate->format('Y-m-d'),
                'warranty_expiry_date' => $warrantyExpiryDate->format('Y-m-d'),
                'purchase_origin_country' => $purchaseOriginCountries[array_rand($purchaseOriginCountries)],

                'department' => $departments[array_rand($departments)],
                'location' => $locations[array_rand($locations)],
                'business_unit' => $businessUnits[array_rand($businessUnits)],
                'plant' => $plants[array_rand($plants)],

                'end_user' => 'Test User ' . $i,
                'employee_id' => 'EMP' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'responsive' => rand(0, 1),

                'next_maintenance' => Carbon::now()->addDays(rand(1, 365))->format('Y-m-d'),
                'operating_system' => $systems[array_rand($systems)],

                'confidentiality' => rand(1, 3),
                'integrity' => rand(1, 3),
                'availability' => rand(1, 3),
                'classification' => rand(1, 3),

                'state' => $states[array_rand($states)],

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}