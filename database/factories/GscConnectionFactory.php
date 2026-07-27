<?php

namespace Database\Factories;

use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

class GscConnectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'website_id' => Website::factory(),
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'token_expires_at' => now()->addHour(),
            'connected_at' => now(),
        ];
    }
}
