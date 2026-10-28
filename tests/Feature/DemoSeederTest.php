<?php

use App\Enums\MilestoneStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('seeds one complete screenshot-ready demo project repeatably', function () {
    $this->seed();
    $this->seed();

    $project = Project::with(['client', 'milestones', 'updates'])->firstOrFail();

    expect(Project::count())->toBe(1)
        ->and(User::where('role', User::ROLE_CLIENT)->count())->toBe(1)
        ->and($project->title)->toBe('Ever After Wedding Photography')
        ->and($project->milestones)->toHaveCount(6)
        ->and($project->updates)->toHaveCount(8)
        ->and($project->milestones[0]->status)->toBe(MilestoneStatus::Done)
        ->and($project->updates[0]->body)->toBe('The homepage and portfolio design are in progress. Next I’ll share the first complete design for review.')
        ->and(Hash::check('password', $project->client->password))->toBeTrue();
});
