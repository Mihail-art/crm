<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['task_id', 'file_path', 'original_name'])]
class TaskAttachment extends Model
{
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
