<?php

namespace Database\Seeders;

use App\Models\GuestAccess;
use Illuminate\Database\Seeder;

class GuestAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GuestAccess::factory()->count(3)->create();
    }
}
