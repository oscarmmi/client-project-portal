<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function show(Request $request): Response
    {
        $project = $request->user()->project()
            ->with([
                'milestones',
                'updates.author:id,name',
            ])
            ->first();

        return Inertia::render('Client/Project/Show', [
            'project' => $project,
        ]);
    }
}
