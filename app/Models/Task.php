<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title', 'description', 'assignee_id', 'creator_id', 'due_date',
    'priority', 'status', 'tag', 'client_name', 'position',
])]
class Task extends Model
{
    public const STATUSES = [
        'new' => 'Нові',
        'in_progress' => 'В роботі',
        'review' => 'На перевірці',
        'done' => 'Виконано',
    ];

    public const STATUS_HEADER_CLASSES = [
        'new' => 'text-secondary-emphasis',
        'in_progress' => 'text-primary',
        'review' => 'text-warning-emphasis',
        'done' => 'text-success-emphasis',
    ];

    public const PRIORITIES = [
        'low' => 'Низький',
        'medium' => 'Середній',
        'high' => 'Високий',
        'urgent' => 'Терміново',
    ];

    public const PRIORITY_CLASSES = [
        'low' => 'bg-secondary-subtle text-secondary-emphasis',
        'medium' => 'bg-warning-subtle text-warning-emphasis',
        'high' => 'bg-primary-subtle text-primary-emphasis',
        'urgent' => 'bg-danger-subtle text-danger-emphasis',
    ];

    public const PRIORITY_BAR_CLASSES = [
        'low' => 'bg-secondary',
        'medium' => 'bg-warning',
        'high' => 'bg-primary',
        'urgent' => 'bg-danger',
    ];

    public const TAGS = [
        'warehouse' => 'Склад',
        'clients' => 'Клієнти',
        'delivery' => 'Доставка',
        'admin' => 'Адмін',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(TaskChecklistItem::class)->orderBy('position');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function priorityBadgeClass(): string
    {
        return self::PRIORITY_CLASSES[$this->priority] ?? 'bg-secondary-subtle';
    }

    public function priorityBarClass(): string
    {
        return self::PRIORITY_BAR_CLASSES[$this->priority] ?? 'bg-secondary';
    }

    public function tagLabel(): ?string
    {
        return $this->tag ? (self::TAGS[$this->tag] ?? $this->tag) : null;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'done';
    }

    public function isDueSoon(): bool
    {
        return $this->due_date
            && ! $this->isOverdue()
            && $this->due_date->isBefore(now()->addDay()->endOfDay())
            && $this->status !== 'done';
    }

    public function dueDateClass(): string
    {
        if ($this->isOverdue()) {
            return 'text-danger';
        }

        if ($this->isDueSoon()) {
            return 'text-warning-emphasis';
        }

        return 'text-muted';
    }

    public function checklistProgress(): string
    {
        $total = $this->checklistItems->count();
        $done = $this->checklistItems->where('is_done', true)->count();

        return "{$done}/{$total}";
    }
}
