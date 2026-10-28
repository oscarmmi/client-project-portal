<?php

use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectUpdate;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shows a client only their own project with progress and updates', function () {
    $client = User::factory()->client()->create();
    $otherClient = User::factory()->client()->create();
    $project = Project::factory()->create([
        'client_user_id' => $client->id,
        'title' => 'Northstar Studio Website',
    ]);
    $otherProject = Project::factory()->create([
        'client_user_id' => $otherClient->id,
        'title' => 'Private project',
    ]);
    $milestone = Milestone::factory()->create([
        'project_id' => $project->id,
        'title' => 'Approve visual direction',
        'status' => 'done',
    ]);
    Milestone::factory()->create([
        'project_id' => $otherProject->id,
        'title' => 'Another client milestone',
    ]);
    $owner = User::factory()->owner()->create();
    ProjectUpdate::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'body' => 'The design review is ready.',
    ]);

    $this->withoutVite()
        ->actingAs($client)
        ->get(route('client.project.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Client/Project/Show')
            ->where('project.id', $project->id)
            ->where('project.title', 'Northstar Studio Website')
            ->where('project.milestones.0.id', $milestone->id)
            ->where('project.milestones.0.status', 'done')
            ->where('project.updates.0.body', 'The design review is ready.')
            ->missing('project.milestones.1')
            ->missing('project.client.email'));
});

it('redirects guests and forbids owners from the client project page', function () {
    $this->get(route('client.project.show'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->owner()->create())
        ->get(route('client.project.show'))
        ->assertForbidden();
});

it('sends signed-in clients to their project from the dashboard', function () {
    $client = User::factory()->client()->create();

    $this->actingAs($client)
        ->get(route('dashboard'))
        ->assertRedirect(route('client.project.show'));
});
