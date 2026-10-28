<?php

namespace Database\Seeders;

use App\Enums\MilestoneStatus;
use App\Models\Project;
use App\Models\ProjectUpdate;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $owner = User::updateOrCreate(
            ['email' => 'owner@example.test'],
            [
                'name' => 'Project Owner',
                'password' => Hash::make('password'),
                'role' => User::ROLE_OWNER,
            ],
        );

        $client = User::updateOrCreate(
            ['email' => 'client@example.test'],
            [
                'name' => 'Emma Wilson',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CLIENT,
            ],
        );

        $project = Project::updateOrCreate(
            ['client_user_id' => $client->id],
            [
                'title' => 'Ever After Wedding Photography',
                'description' => 'A warm, editorial website for a wedding photographer, with a portfolio of celebrations, information for couples, and a simple way to get in touch.',
            ],
        );

        $project->milestones()->delete();
        $project->updates()->delete();

        $milestones = [
            ['title' => 'Project kickoff and story gathering', 'description' => 'Align on the photographer’s approach, ideal couples, and the stories the site should tell.', 'status' => MilestoneStatus::Done, 'due_date' => today()->subDays(8)],
            ['title' => 'Visual direction and moodboard', 'description' => 'Set the editorial look, typography, and image treatment for the new site.', 'status' => MilestoneStatus::Done, 'due_date' => today()->subDays(3)],
            ['title' => 'Homepage and portfolio design', 'description' => 'Shape the homepage, featured galleries, and the first impression for new visitors.', 'status' => MilestoneStatus::InProgress, 'due_date' => today()->addDays(3)],
            ['title' => 'Wedding story and gallery pages', 'description' => 'Build flexible galleries that let each celebration feel personal.', 'status' => MilestoneStatus::Pending, 'due_date' => today()->addDays(8)],
            ['title' => 'Mobile refinement and contact flow', 'description' => 'Make the experience feel effortless on phones and simplify enquiries.', 'status' => MilestoneStatus::Pending, 'due_date' => today()->addDays(12)],
            ['title' => 'Final review and launch', 'description' => 'Review the finished pages together, make final adjustments, and prepare for launch.', 'status' => MilestoneStatus::Pending, 'due_date' => today()->addDays(16)],
        ];

        foreach ($milestones as $index => $milestone) {
            $project->milestones()->create([
                ...$milestone,
                'sort_order' => $index,
            ]);
        }

        $updates = [
            'We’re underway. I’ve gathered the project goals and the moments that make your photography distinctive.',
            'The initial content outline is ready, with a clear path from the homepage into the wedding stories.',
            'I’ve reviewed the image collection and pulled together a few directions for the site’s visual tone.',
            'The moodboard is ready. The direction pairs warm neutrals with generous space for the photographs.',
            'The visual direction is approved. I’m now applying it to the homepage and first portfolio layout.',
            'The first homepage composition is taking shape, including the featured story and enquiry path.',
            'I’ve started refining the gallery pages so each celebration can have its own rhythm and detail.',
            'The homepage and portfolio design are in progress. Next I’ll share the first complete design for review.',
        ];

        foreach ($updates as $index => $body) {
            $update = ProjectUpdate::create([
                'project_id' => $project->id,
                'user_id' => $owner->id,
                'body' => $body,
            ]);
            $timestamp = now()->subDays(count($updates) - $index - 1);
            $update->forceFill([
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])->save();
        }

        $this->command?->info('Demo logins: owner@example.test / password (owner), client@example.test / password (client).');
    }
}
