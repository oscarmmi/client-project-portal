<?php

namespace App\Http\Controllers\Owner;

use App\Enums\MilestoneStatus;
use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Owner/Project/Show', [
            'project' => Project::with([
                'client:id,name,email',
                'milestones',
                'updates.author:id,name',
            ])->first(),
            'clients' => User::query()
                ->where('role', User::ROLE_CLIENT)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'milestoneStatuses' => array_map(
                fn (MilestoneStatus $status) => ['value' => $status->value, 'label' => str_replace('_', ' ', ucfirst($status->value))],
                MilestoneStatus::cases(),
            ),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(Project::query()->exists(), 409, 'Only one active project is supported.');

        $data = $request->validate([
            'client_user_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', User::ROLE_CLIENT),
                Rule::unique('projects', 'client_user_id'),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
        ]);

        Project::create($data);

        return to_route('owner.project.show')->with('success', 'Project created.');
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'client_user_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', User::ROLE_CLIENT),
                Rule::unique('projects', 'client_user_id')->ignore($project->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
        ]);

        $project->update($data);

        return to_route('owner.project.show')->with('success', 'Project details saved.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return to_route('owner.project.show')->with('success', 'Project deleted.');
    }

    public function storeMilestone(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::enum(MilestoneStatus::class)],
            'due_date' => ['nullable', 'date'],
        ]);

        $data['sort_order'] = (int) $project->milestones()->max('sort_order') + 1;
        $project->milestones()->create($data);

        return to_route('owner.project.show')->with('success', 'Milestone added.');
    }

    public function updateMilestone(Request $request, Project $project, Milestone $milestone): RedirectResponse
    {
        abort_unless($milestone->project_id === $project->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::enum(MilestoneStatus::class)],
            'due_date' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
        ]);

        $milestone->update($data);

        return to_route('owner.project.show')->with('success', 'Milestone updated.');
    }

    public function destroyMilestone(Project $project, Milestone $milestone): RedirectResponse
    {
        abort_unless($milestone->project_id === $project->id, 404);

        $milestone->delete();

        return to_route('owner.project.show')->with('success', 'Milestone removed.');
    }

    public function storeUpdate(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $project->updates()->create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return to_route('owner.project.show')->with('success', 'Project update posted.');
    }
}
