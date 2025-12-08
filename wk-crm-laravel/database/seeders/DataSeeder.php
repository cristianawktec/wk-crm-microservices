<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Seller;
use App\Models\Opportunity;

class DataSeeder extends Seeder
{
    public function run(): void
    {
        // Create customers
        Customer::factory()->count(10)->create();
        
        // Create sellers
        Seller::factory()->count(5)->create();
        
        // Create leads
        Lead::factory()->count(20)->create();
        
        // Create opportunities
        Opportunity::factory()->count(15)->create();
    }
}
