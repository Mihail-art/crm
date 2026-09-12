<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklistItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $query = Task::query()->with(['assignee', 'creator', 'checklistItems']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($assignee = $request->integer('assignee')) {
            $query->where('assignee_id', $assignee);
        }

        if ($priority = $request->string('priority')->toString()) {
            $query->where('priority', $priority);
        }

        match ($request->string('period')->toString()) {
            'today' => $query->whereDate('due_date', now()->toDateString()),
            'week' => $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]),
            default => null,
        };

        $tasks = $query->orderBy('position')->get();

        $overdueCount = Task::where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        return view('pages.tasks.index', [
            'tasks' => $tasks,
            'tasksByStatus' => $tasks->groupBy('status'),
            'users' => User::orderBy('name')->get(),
            'filters' => $request->only(['search', 'assignee', 'priority', 'period']),
            'overdueCount' => $overdueCount,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $maxPosition = Task::where('status', $validated['status'] ?? 'new')->max('position') ?? -1;

        Task::create([
            ...$validated,
            'creator_id' => auth()->id(),
            'position' => $maxPosition + 1,
        ]);

        return redirect()->route('tasks')->with('status', 'Задачу створено.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $task->update($this->validated($request, $task));

        return redirect()->route('tasks')->with('status', 'Задачу оновлено.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        foreach ($task->attachments as $attachment) {
            Storage::disk('public')->delete(substr($attachment->file_path, strlen('storage/')));
        }

        $task->delete();

        return redirect()->route('tasks')->with('status', 'Задачу видалено.');
    }

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(Task::STATUSES))],
            'position' => ['nullable', 'integer'],
        ]);

        $task->update([
            'status' => $validated['status'],
            'position' => $validated['position'] ?? $task->position,
        ]);

        return response()->json(['status' => $task->status]);
    }

    public function addChecklistItem(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate(['title' => ['required', 'string', 'max:255']]);

        $maxPosition = $task->checklistItems()->max('position') ?? -1;
        $item = $task->checklistItems()->create([
            'title' => $validated['title'],
            'position' => $maxPosition + 1,
        ]);

        return response()->json([
            'item' => $item,
            'progress' => $task->fresh('checklistItems')->checklistProgress(),
        ]);
    }

    public function toggleChecklistItem(TaskChecklistItem $item): JsonResponse
    {
        $item->update(['is_done' => ! $item->is_done]);

        return response()->json([
            'is_done' => $item->is_done,
            'progress' => $item->task->fresh('checklistItems')->checklistProgress(),
        ]);
    }

    public function destroyChecklistItem(TaskChecklistItem $item): JsonResponse
    {
        $task = $item->task;
        $item->delete();

        return response()->json([
            'progress' => $task->fresh('checklistItems')->checklistProgress(),
        ]);
    }

    public function addComment(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate(['body' => ['required', 'string']]);

        $comment = $task->comments()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);
        $comment->load('user');

        return response()->json([
            'id' => $comment->id,
            'body' => $comment->body,
            'user_name' => $comment->user?->name ?? 'Користувач',
            'created_at' => $comment->created_at->format('d.m.Y H:i'),
        ]);
    }

    public function addAttachment(Request $request, Task $task): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'max:10240']]);

        $file = $request->file('file');
        $path = $file->store('task-attachments', 'public');

        $attachment = $task->attachments()->create([
            'file_path' => 'storage/'.$path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return response()->json([
            'id' => $attachment->id,
            'name' => $attachment->original_name,
            'url' => asset($attachment->file_path),
        ]);
    }

    public function destroyAttachment(TaskAttachment $attachment): JsonResponse
    {
        Storage::disk('public')->delete(substr($attachment->file_path, strlen('storage/')));
        $attachment->delete();

        return response()->json(['ok' => true]);
    }

    private function validated(Request $request, ?Task $task = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', 'in:'.implode(',', array_keys(Task::PRIORITIES))],
            'status' => ['required', 'in:'.implode(',', array_keys(Task::STATUSES))],
            'tag' => ['nullable', 'in:'.implode(',', array_keys(Task::TAGS))],
            'client_name' => ['nullable', 'string', 'max:255'],
        ], [
            'title.required' => 'Введіть назву задачі.',
            'assignee_id.exists' => 'Оберіть коректного виконавця.',
            'priority.required' => 'Оберіть пріоритет.',
            'status.required' => 'Оберіть статус.',
        ]);
    }
}
