<?php

use App\Enums\MilestoneStatus;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectUpdate;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the owner project management page', function () {
    $owner = User::factory()->owner()->create();
    $client = User::factory()->client()->create();
    $project = Project::factory()->create(['client_user_id' => $client->id]);

    $this->actingAs($owner)
        ->get(route('owner.project.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Owner/Project/Show')
            ->where('project.id', $project->id)
            ->where('clients.0.id', $client->id)
            ->has('milestoneStatuses', 3));
});

it('lets an owner post a timestamped project update', function () {
    $owner = User::factory()->owner()->create();
    $project = Project::factory()->create();

    $this->actingAs($owner)
        ->post(route('owner.updates.store', $project), [
            'body' => 'The first design review is ready for feedback.',
        ])
        ->assertRedirect(route('owner.project.show'));

    $this->assertDatabaseHas('project_updates', [
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'body' => 'The first design review is ready for feedback.',
    ]);
});

it('shows project updates newest first', function () {
    $owner = User::factory()->owner()->create();
    $project = Project::factory()->create();

    $olderUpdate = ProjectUpdate::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'body' => 'Kickoff complete.',
    ]);
    $olderUpdate->forceFill(['created_at' => now()->subDay()])->save();

    ProjectUpdate::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'body' => 'First design review is ready.',
    ]);

    $this->actingAs($owner)
        ->get(route('owner.project.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('project.updates.0.body', 'First design review is ready.')
            ->where('project.updates.1.body', 'Kickoff complete.'));
});

it('lets an owner create one project for a client', function () {
    $owner = User::factory()->owner()->create();
    $client = User::factory()->client()->create();

    $this->actingAs($owner)
        ->post(route('owner.project.store'), [
            'client_user_id' => $client->id,
            'title' => 'Northstar Studio Website',
            'description' => 'A new digital home for Northstar Studio.',
        ])
        ->assertRedirect(route('owner.project.show'));

    $this->assertDatabaseHas('projects', [
        'client_user_id' => $client->id,
        'title' => 'Northstar Studio Website',
    ]);
});

it('lets an owner edit and delete the project', function () {
    $owner = User::factory()->owner()->create();
    $client = User::factory()->client()->create();
    $project = Project::factory()->create(['client_user_id' => $client->id]);

    $this->actingAs($owner)
        ->patch(route('owner.project.update', $project), [
            'client_user_id' => $client->id,
            'title' => 'Updated project title',
            'description' => 'Updated project brief.',
        ])
        ->assertRedirect(route('owner.project.show'));

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'title' => 'Updated project title',
    ]);

    $this->delete(route('owner.project.destroy', $project))
        ->assertRedirect(route('owner.project.show'));

    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});

it('prevents a second active project', function () {
    $owner = User::factory()->owner()->create();
    $firstClient = User::factory()->client()->create();
    $secondClient = User::factory()->client()->create();
    Project::factory()->create(['client_user_id' => $firstClient->id]);

    $this->actingAs($owner)
        ->post(route('owner.project.store'), [
            'client_user_id' => $secondClient->id,
            'title' => 'Another project',
        ])
        ->assertStatus(409);
});

it('lets an owner add and update a milestone status', function () {
    $owner = User::factory()->owner()->create();
    $client = User::factory()->client()->create();
    $project = Project::factory()->create(['client_user_id' => $client->id]);

    $this->actingAs($owner)
        ->post(route('owner.milestones.store', $project), [
            'title' => 'Approve the visual direction',
            'status' => MilestoneStatus::Pending->value,
        ])
        ->assertRedirect(route('owner.project.show'));

    $milestone = $project->milestones()->firstOrFail();

    $this->patch(route('owner.milestones.update', [$project, $milestone]), [
        'title' => $milestone->title,
        'status' => MilestoneStatus::Done->value,
        'sort_order' => $milestone->sort_order,
    ])->assertRedirect(route('owner.project.show'));

    expect($milestone->fresh()->status)->toBe(MilestoneStatus::Done);
});

it('does not let an owner edit a milestone belonging to another project', function () {
    $owner = User::factory()->owner()->create();
    $firstProject = Project::factory()->create([
        'client_user_id' => User::factory()->client()->create()->id,
    ]);
    $secondProject = Project::factory()->create([
        'client_user_id' => User::factory()->client()->create()->id,
    ]);
    $milestone = Milestone::factory()->create(['project_id' => $secondProject->id]);

    $this->actingAs($owner)
        ->patch(route('owner.milestones.update', [$firstProject, $milestone]), [
            'title' => $milestone->title,
            'status' => MilestoneStatus::Done->value,
            'sort_order' => $milestone->sort_order,
        ])
        ->assertNotFound();
});
