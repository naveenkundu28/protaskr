<?php

namespace App\Traits;

use App\Models\Story;
use App\Models\Task;

trait EnsuresStoryIsEditable
{
    protected function ensureStoryIsEditable(Story $story): void
    {
        abort_if($story->status === 'completed', 403, 'This story is completed. Reopen it before making changes.');
    }

    protected function ensureTaskStoryIsEditable(Task $task, bool $allowCompletedTask = false): void
    {
        if (!$allowCompletedTask) {
            abort_if($task->status === 'completed', 403, 'This task is completed and cannot be changed.');
        }

        abort_if(
            $task->story()->where('status', 'completed')->exists(),
            403,
            'This story is completed. Reopen it before making changes to its tasks.'
        );
    }
}
