<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk model Task.
 * Digunakan oleh test SRS-02 (cascade delete) untuk membuat task di dalam list.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_list_id' => TaskList::factory(),
            'user_id'      => User::factory(),
            'title'        => fake()->sentence(4),
            'description'  => fake()->optional()->paragraph(),
            'priority'     => fake()->randomElement(TaskPriority::cases()),
            'status'       => TaskStatus::Pending,
            'due_date'     => fake()->optional()->dateTimeBetween('now', '+30 days'),
        ];
    }

    /**
     * State: task sudah selesai (completed).
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => TaskStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
