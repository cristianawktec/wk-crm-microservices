<?php

namespace Database\Factories;

use App\Models\Opportunity;
use App\Models\Customer;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OpportunityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Opportunity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'title' => $this->faker->sentence(3),
            'customer_id' => Customer::factory(),
            'seller_id' => Seller::factory(),
            'value' => $this->faker->randomFloat(2, 1000, 100000),
            'currency' => 'BRL',
            'probability' => $this->faker->randomElement([0, 25, 50, 75, 100]),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'won', 'lost']),
            'close_date' => $this->faker->dateTimeBetween('now', '+30 days'),
        ];
    }
}
