<?php

namespace Database\Factories;

use App\Enums\Goal;
use App\Enums\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'level' => Level::map()[array_rand(Level::map())],
            'goal' => Goal::map()[array_rand(Goal::map())],
            'slug' => $this->faker->slug(),
            'feature_img' => $this->faker->imageUrl(),
            'duration' => $this->faker->numberBetween(30, 60),
            'status' => 'active',
        ];
    }
}
