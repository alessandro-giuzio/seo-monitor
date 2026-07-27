<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WebsiteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'base_url' => 'https://'.fake()->unique()->domainName(),
            'industry' => fake()->word(),
            'target_country' => 'US',
            'crawl_frequency_hours' => 24,
        ];
    }
}
