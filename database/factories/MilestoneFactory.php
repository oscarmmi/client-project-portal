<?php

namespace Database\Factories;

use App\Enums\MilestoneStatus;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Milestone>
 */
class MilestoneFactory extends Factory
{
    protected $model = Milestone::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(MilestoneStatus::cases()),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+3 months'),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
