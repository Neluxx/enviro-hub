<?php

namespace Database\Factories;

use App\Models\Home;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Home>
 */
class HomeFactory extends Factory
{
    protected $model = Home::class;

    /**
     * @inheritdoc
     */
    public function definition(): array
    {
        return [
            'title' => fake()->company() . ' Home',
            'identifier' => Str::uuid()->toString(),
        ];
    }
}
