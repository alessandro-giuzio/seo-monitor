<?php

namespace Database\Factories;

use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompetitorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'website_id' => Website::factory(),
            'name' => fake()->company(),
            'domain' => fake()->unique()->domainName(),
        ];
    }
}
