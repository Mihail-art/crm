@extends('layouts.app')

@section('title', 'Задачі')

@push('scripts')
  @vite(['resources/js/tasks.js'])
@endpush

@section('content')

  @if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('status') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="row align-items-center mb-4">
    <div class="col">
      <div class="d-flex align-items-center gap-2">
        <h1 class="fs-3 mb-0">Задачі</h1>
        @if ($overdueCount > 0)
          <span class="badge bg-danger-subtle text-danger">{{ $overdueCount }} прострочено</span>
        @endif
      </div>
    </div>
    <div class="col-auto">
      <button type="button" class="btn btn-primary" id="newTaskBtn">
        <i class="ti ti-plus me-1"></i> Нова задача
      </button>
    </div>
  </div>

  <form method="GET" action="{{ route('tasks') }}" id="filtersForm" class="row g-2 align-items-center mb-4">
    <div class="col-12 col-md-3">
      <div class="input-group">
        <span class="input-group-text bg-body-tertiary border-end-0"><i class="ti ti-search"></i></span>
        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Пошук за назвою" value="{{ $filters['search'] ?? '' }}">
      </div>
    </div>
    <div class="col-6 col-md-3">
      <x-custom-select
          name="assignee"
          :options="$users->pluck('name', 'id')"
          :selected="$filters['assignee'] ?? ''"
          placeholder="Усі виконавці"
          onchange="this.form.submit()"
      />
    </div>
    <div class="col-6 col-md-2">
      <x-custom-select
          name="priority"
          :options="\App\Models\Task::PRIORITIES"
          :selected="$filters['priority'] ?? ''"
          placeholder="Усі пріоритети"
          onchange="this.form.submit()"
      />
    </div>
    <div class="col-6 col-md-2">
      <x-custom-select
          name="period"
          :options="['today' => 'Сьогодні', 'week' => 'Цей тиждень']"
          :selected="$filters['period'] ?? ''"
          placeholder="Усі"
          onchange="this.form.submit()"
      />
    </div>
    <div class="col-6 col-md-2 d-flex justify-content-md-end">
      <div class="btn-group" role="group" aria-label="Вигляд">
        <button type="button" class="btn btn-outline-secondary" id="viewKanbanBtn" title="Канбан"><i class="ti ti-layout-kanban"></i></button>
        <button type="button" class="btn btn-outline-secondary" id="viewListBtn" title="Список"><i class="ti ti-list"></i></button>
      </div>
    </div>
  </form>

  <!-- KANBAN VIEW -->
  <div id="tasksKanbanView" class="kanban-board">
    @foreach (\App\Models\Task::STATUSES as $status => $label)
      <div class="kanban-column">
        <div class="kanban-column-header d-flex justify-content-between align-items-center px-2 py-2">
          <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold small {{ \App\Models\Task::STATUS_HEADER_CLASSES[$status] }}">{{ $label }}</span>
            <span class="badge bg-body-secondary text-body-emphasis column-count-badge">{{ $tasksByStatus->get($status, collect())->count() }}</span>
          </div>
          <button type="button" class="btn btn-sm btn-icon btn-light quick-add-btn" data-status="{{ $status }}" title="Додати задачу">
            <i class="ti ti-plus"></i>
          </button>
        </div>
        <div class="kanban-column-body" data-status="{{ $status }}">
          @forelse ($tasksByStatus->get($status, collect()) as $task)
            @php
              $jsTask = [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'assignee_id' => $task->assignee_id,
                'priority' => $task->priority,
                'status' => $task->status,
                'tag' => $task->tag,
                'client_name' => $task->client_name,
                'due_date_input' => optional($task->due_date)->format('Y-m-d\TH:i'),
                'creator_name' => $task->creator?->name,
                'update_url' => route('tasks.update', $task),
                'destroy_url' => route('tasks.destroy', $task),
                'checklist_items' => $task->checklistItems->map(fn ($i) => ['id' => $i->id, 'title' => $i->title, 'is_done' => $i->is_done])->values(),
                'comments' => $task->comments->map(fn ($c) => ['id' => $c->id, 'body' => $c->body, 'user_name' => $c->user?->name ?? 'Користувач', 'created_at' => $c->created_at->format('d.m.Y H:i')])->values(),
                'attachments' => $task->attachments->map(fn ($a) => ['id' => $a->id, 'name' => $a->original_name, 'url' => asset($a->file_path)])->values(),
              ];
            @endphp
            <div class="card task-card mb-2" draggable="true" data-task-id="{{ $task->id }}" data-task='@json($jsTask)'>
              <div class="task-priority-bar {{ $task->priorityBarClass() }}"></div>
              <div class="card-body p-3">
                <div class="fw-semibold small task-title-clamp mb-1">{{ $task->title }}</div>
                @if ($task->description)
                  <div class="small text-muted text-truncate mb-2">{{ $task->description }}</div>
                @endif
                <div class="d-flex align-items-center gap-2 mb-2">
                  @if ($task->tag)
                    <span class="badge bg-body-secondary text-body-emphasis">{{ $task->tagLabel() }}</span>
                  @endif
                  @if ($task->client_name)
                    <span class="small text-muted" title="{{ $task->client_name }}"><i class="ti ti-link"></i></span>
                  @endif
                </div>
                <div class="d-flex align-items-center justify-content-between">
                  @if ($task->assignee)
                    <x-user-avatar :user="$task->assignee" :size="24" />
                  @else
                    <span></span>
                  @endif
                  @if ($task->due_date)
                    <span class="small {{ $task->dueDateClass() }}"><i class="ti ti-calendar"></i> {{ $task->due_date->format('d.m H:i') }}</span>
                  @endif
                </div>
              </div>
            </div>
          @empty
            <div class="kanban-empty">Немає задач</div>
          @endforelse
        </div>
      </div>
    @endforeach
  </div>

  <!-- LIST VIEW -->
  <div id="tasksListView" class="d-none">
    @php
      $now = now();
      $groups = [
        ['label' => 'Прострочені', 'overdue' => true, 'items' => $tasks->filter(fn ($t) => $t->isOverdue())],
        ['label' => 'Сьогодні', 'overdue' => false, 'items' => $tasks->filter(fn ($t) => $t->due_date && $t->due_date->isToday() && ! $t->isOverdue())],
        ['label' => 'Завтра', 'overdue' => false, 'items' => $tasks->filter(fn ($t) => $t->due_date && $t->due_date->isTomorrow())],
        ['label' => 'На цьому тижні', 'overdue' => false, 'items' => $tasks->filter(fn ($t) => $t->due_date && $t->due_date->isAfter($now->copy()->addDay()->endOfDay()) && $t->due_date->lessThanOrEqualTo($now->copy()->endOfWeek()))],
        ['label' => 'Пізніше', 'overdue' => false, 'items' => $tasks->filter(fn ($t) => $t->due_date && $t->due_date->isAfter($now->copy()->endOfWeek()))],
        ['label' => 'Без дедлайну', 'overdue' => false, 'items' => $tasks->filter(fn ($t) => ! $t->due_date)],
      ];
    @endphp

    @if ($tasks->isEmpty())
      <div class="card">
        <div class="card-body py-5 text-center">
          <div class="icon-shape icon-xxl bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4">
            <i class="ti ti-checklist fs-1"></i>
          </div>
          <h3 class="h5 mb-2">Задач поки немає</h3>
          <p class="text-muted mb-0">Створіть першу задачу, щоб почати планування роботи.</p>
        </div>
      </div>
    @endif

    @foreach ($groups as $group)
      @continue($group['items']->isEmpty())
      <div class="list-group-header {{ $group['overdue'] ? 'is-overdue' : '' }}">{{ $group['label'] }} ({{ $group['items']->count() }})</div>
      <div class="card mb-4">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <tbody>
              @foreach ($group['items'] as $task)
                @php
                  $jsTask = [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'assignee_id' => $task->assignee_id,
                    'priority' => $task->priority,
                    'status' => $task->status,
                    'tag' => $task->tag,
                    'client_name' => $task->client_name,
                    'due_date_input' => optional($task->due_date)->format('Y-m-d\TH:i'),
                    'creator_name' => $task->creator?->name,
                    'update_url' => route('tasks.update', $task),
                    'destroy_url' => route('tasks.destroy', $task),
                    'checklist_items' => $task->checklistItems->map(fn ($i) => ['id' => $i->id, 'title' => $i->title, 'is_done' => $i->is_done])->values(),
                    'comments' => $task->comments->map(fn ($c) => ['id' => $c->id, 'body' => $c->body, 'user_name' => $c->user?->name ?? 'Користувач', 'created_at' => $c->created_at->format('d.m.Y H:i')])->values(),
                    'attachments' => $task->attachments->map(fn ($a) => ['id' => $a->id, 'name' => $a->original_name, 'url' => asset($a->file_path)])->values(),
                  ];
                @endphp
                <tr class="task-row {{ $group['overdue'] ? 'table-danger' : '' }}" data-task='@json($jsTask)'>
                  <td style="width:36px;">
                    <input type="checkbox" class="form-check-input" @checked($task->status === 'done') disabled>
                  </td>
                  <td>
                    <div class="fw-semibold small">{{ $task->title }}</div>
                    @if ($task->tag)<span class="badge bg-body-secondary text-body-emphasis">{{ $task->tagLabel() }}</span>@endif
                  </td>
                  <td>
                    @if ($task->assignee)
                      <div class="d-flex align-items-center gap-2">
                        <x-user-avatar :user="$task->assignee" :size="24" />
                        <span class="small">{{ $task->assignee->name }}</span>
                      </div>
                    @else
                      <span class="text-muted small">—</span>
                    @endif
                  </td>
                  <td class="small {{ $task->dueDateClass() }}">
                    {{ $task->due_date?->format('d.m.Y H:i') ?? '—' }}
                  </td>
                  <td><span class="badge {{ $task->priorityBadgeClass() }}">{{ $task->priorityLabel() }}</span></td>
                  <td><span class="badge bg-body-secondary text-body-emphasis">{{ $task->statusLabel() }}</span></td>
                  <td class="text-end" onclick="event.stopPropagation()">
                    <div class="dropdown">
                      <button class="btn btn-sm btn-light btn-icon" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                          <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="delete-task-form-list">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger"><i class="ti ti-trash me-2"></i>Видалити</button>
                          </form>
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endforeach
  </div>

  <!-- QUICK CREATE MODAL -->
  <div class="modal fade" id="taskCreateModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="{{ route('tasks.store') }}">
          @csrf
          <input type="hidden" name="status" id="quickCreateStatus" value="new">
          <div class="modal-header">
            <h5 class="modal-title">Нова задача</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Назва задачі</label>
              <input type="text" name="title" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
              <label class="form-label">Виконавець</label>
              <x-custom-select name="assignee_id" :options="$users->pluck('name', 'id')" />
            </div>
            <div class="mb-3">
              <label class="form-label">Дедлайн</label>
              <input type="datetime-local" name="due_date" class="form-control" required>
            </div>
            <div class="mb-1">
              <label class="form-label">Пріоритет</label>
              <x-custom-select name="priority" :options="\App\Models\Task::PRIORITIES" selected="medium" />
            </div>

            <a href="#" id="toggleMoreFields" class="small link-primary d-inline-block mt-2 mb-2">Більше полів</a>

            <div id="moreFieldsWrapper" class="d-none">
              <div class="mb-3">
                <label class="form-label">Опис</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Тег</label>
                <x-custom-select name="tag" :options="\App\Models\Task::TAGS" placeholder="Без тегу" />
              </div>
              <div class="mb-3">
                <label class="form-label">Пов'язаний клієнт</label>
                <input type="text" name="client_name" class="form-control" placeholder="Назва клієнта">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Скасувати</button>
            <button type="submit" class="btn btn-primary">Створити</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- DETAILS OFFCANVAS -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="taskDetailsOffcanvas" style="width: 440px;">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title">Деталі задачі</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <form id="taskDetailsForm" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" id="detailsTaskId">

        <div class="mb-3">
          <input type="text" name="title" id="detailsTitleInput" class="form-control fw-semibold border-0 px-0 fs-5" required>
        </div>
        <p class="small text-muted mb-3" id="detailsCreator"></p>

        <div class="mb-3">
          <label class="form-label">Опис</label>
          <textarea name="description" id="detailsDescription" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Виконавець</label>
          <x-custom-select name="assignee_id" id="detailsAssignee" :options="$users->pluck('name', 'id')" placeholder="Не призначено" />
        </div>

        <div class="mb-3">
          <label class="form-label">Дедлайн</label>
          <input type="datetime-local" name="due_date" id="detailsDueDate" class="form-control">
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Пріоритет</label>
            <x-custom-select name="priority" id="detailsPriority" :options="\App\Models\Task::PRIORITIES" />
          </div>
          <div class="col-6">
            <label class="form-label">Статус</label>
            <x-custom-select name="status" id="detailsStatus" :options="\App\Models\Task::STATUSES" />
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Тег</label>
          <x-custom-select name="tag" id="detailsTag" :options="\App\Models\Task::TAGS" placeholder="Без тегу" />
        </div>

        <div class="mb-4">
          <label class="form-label">Пов'язаний клієнт</label>
          <input type="text" name="client_name" id="detailsClientName" class="form-control" placeholder="Назва клієнта">
        </div>

        <div class="d-flex gap-2 mb-4">
          <form id="deleteTaskForm" method="POST" class="flex-grow-1">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Видалити задачу</button>
          </form>
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Зберегти</button>
        </div>
      </form>

      <hr>

      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0">Чек-лист</h6>
          <span class="small text-muted" id="checklistProgress">0/0 виконано</span>
        </div>
        <div id="checklistItems" class="d-flex flex-column gap-2 mb-2"></div>
        <div class="input-group input-group-sm">
          <input type="text" id="newChecklistItem" class="form-control" placeholder="Новий пункт">
          <button type="button" class="btn btn-outline-secondary" id="addChecklistItemBtn"><i class="ti ti-plus"></i></button>
        </div>
      </div>

      <div class="mb-4">
        <h6 class="mb-2">Вкладення</h6>
        <div id="attachmentsList" class="mb-2"></div>
        <label class="btn btn-outline-secondary btn-sm">
          <i class="ti ti-paperclip me-1"></i> Додати файл
          <input type="file" id="attachmentInput" class="d-none">
        </label>
      </div>

      <div>
        <h6 class="mb-2">Коментарі</h6>
        <div id="commentsList" class="mb-3" style="max-height: 240px; overflow-y: auto;"></div>
        <div class="input-group input-group-sm">
          <input type="text" id="newComment" class="form-control" placeholder="Написати коментар...">
          <button type="button" class="btn btn-primary" id="addCommentBtn"><i class="ti ti-send"></i></button>
        </div>
      </div>
    </div>
  </div>

@endsection
