<?php

namespace Database\Factories;

use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

class GscMetricFactory extends Factory
{
    public function definition(): array
    {
        return [
            'website_id' => Website::factory(),
            'metric_date' => fake()->dateTimeBetween('-60 days', 'now')->format('Y-m-d'),
            'query' => fake()->words(3, true),
            'page_url' => fake()->url(),
            'clicks' => fake()->numberBetween(0, 100),
            'impressions' => fake()->numberBetween(0, 1000),
            'ctr' => fake()->randomFloat(4, 0, 1),
            'avg_position' => fake()->randomFloat(2, 1, 50),
        ];
    }
}
