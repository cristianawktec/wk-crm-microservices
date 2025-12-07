<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'source' => $this->faker->randomElement(['web', 'referral', 'event', 'outbound']),
            'status' => $this->faker->randomElement(['new', 'contacted', 'qualified', 'lost']),
            'seller_id' => Seller::factory(),
        ];
    }
}
