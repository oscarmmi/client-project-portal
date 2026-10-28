<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'title', 'description', 'status', 'due_date', 'sort_order'])]
class Milestone extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => MilestoneStatus::class,
            'due_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
