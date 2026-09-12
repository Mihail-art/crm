@extends('layouts.app')

@section('title', 'Команда')

@push('scripts')
  @vite(['resources/js/team.js'])
@endpush

@section('content')

  @if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('status') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="row align-items-center mb-4">
    <div class="col">
      <h1 class="fs-3 mb-0">Команда</h1>
    </div>
    <div class="col-auto">
      @if ($limitReached)
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-secondary" disabled title="Ліміт тарифу вичерпано">
            <i class="ti ti-lock me-1"></i> Додати співробітника
          </button>
          <a href="#" class="small link-primary">Оновити план</a>
        </div>
      @else
        <button type="button" class="btn btn-primary" id="addMemberBtn">
          <i class="ti ti-plus me-1"></i> Додати співробітника
        </button>
      @endif
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="card p-3 h-100">
        <div class="d-flex align-items-center gap-3">
          <div class="icon-shape icon-md bg-primary bg-opacity-10 text-primary rounded-2">
            <i class="ti ti-users fs-5"></i>
          </div>
          <div>
            <div class="text-muted small">Всього співробітників</div>
            <div class="fs-4 fw-bold">{{ $totalCount }}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card p-3 h-100">
        <div class="d-flex align-items-center gap-3">
          <div class="icon-shape icon-md bg-success bg-opacity-10 text-success rounded-2">
            <i class="ti ti-user-check fs-5"></i>
          </div>
          <div>
            <div class="text-muted small">Активних зараз</div>
            <div class="fs-4 fw-bold d-flex align-items-center gap-2">
              {{ $activeCount }}
              <span class="stock-dot stock-dot-in"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card p-3 h-100">
        <div class="d-flex align-items-center gap-3">
          <div class="icon-shape icon-md bg-info bg-opacity-10 text-info rounded-2">
            <i class="ti ti-briefcase fs-5"></i>
          </div>
          <div>
            <div class="text-muted small">Менеджерів</div>
            <div class="fs-4 fw-bold">{{ $managerCount }}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card p-3 h-100">
        <div class="text-muted small mb-2">Ліміт по тарифу</div>
        <div class="fw-semibold mb-2">{{ $totalCount }} з {{ $seatLimit }} доступних місць</div>
        <div class="progress" style="height: 6px;">
          <div class="progress-bar {{ $limitReached ? 'bg-danger' : 'bg-primary' }}" style="width: {{ min(100, ($totalCount / $seatLimit) * 100) }}%"></div>
        </div>
      </div>
    </div>
  </div>

  <form method="GET" action="{{ route('team') }}" id="filtersForm" class="row g-2 align-items-center mb-4">
    <div class="col-12 col-md-4">
      <div class="input-group">
        <span class="input-group-text bg-body-tertiary border-end-0"><i class="ti ti-search"></i></span>
        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Пошук за іменем або email" value="{{ $filters['search'] ?? '' }}">
      </div>
    </div>
    <div class="col-6 col-md-3">
      <x-custom-select
          name="role"
          :options="\App\Models\User::ROLES"
          :selected="$filters['role'] ?? ''"
          placeholder="Усі ролі"
          onchange="this.form.submit()"
      />
    </div>
    <div class="col-6 col-md-3">
      <x-custom-select
          name="status"
          :options="['active' => 'Активні', 'blocked' => 'Заблоковані']"
          :selected="$filters['status'] ?? ''"
          placeholder="Усі статуси"
          onchange="this.form.submit()"
      />
    </div>
    <div class="col-12 col-md-2 d-flex justify-content-md-end">
      <div class="btn-group" role="group" aria-label="Вигляд">
        <button type="button" class="btn btn-outline-secondary" id="viewTableBtn" title="Таблиця"><i class="ti ti-list"></i></button>
        <button type="button" class="btn btn-outline-secondary" id="viewGridBtn" title="Картки"><i class="ti ti-layout-grid"></i></button>
      </div>
    </div>
  </form>

  @if ($totalCount <= 1 && ! array_filter($filters))
    <div class="card">
      <div class="card-body py-5 text-center">
        <div class="icon-shape icon-xxl bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4">
          <i class="ti ti-users-group fs-1"></i>
        </div>
        <h3 class="h5 mb-2">Запросіть свою команду</h3>
        <p class="text-muted mb-4">Додайте співробітників, щоб працювати разом над клієнтами та угодами.</p>
        <button type="button" class="btn btn-primary" id="emptyAddMemberBtn"><i class="ti ti-plus me-1"></i>Додати першого співробітника</button>
      </div>
    </div>
  @elseif ($team->isEmpty())
    <div class="card">
      <div class="card-body py-5 text-center">
        <div class="icon-shape icon-xxl bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4">
          <i class="ti ti-search-off fs-1"></i>
        </div>
        <h3 class="h5 mb-2">Нічого не знайдено</h3>
        <p class="text-muted mb-4">Спробуйте змінити параметри пошуку або фільтри.</p>
        <a href="{{ route('team') }}" class="btn btn-outline-secondary">Скинути фільтри</a>
      </div>
    </div>
  @else

    <!-- TABLE VIEW -->
    <div id="teamTableView" class="card">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th style="width:56px;"></th>
              <th>Ім'я</th>
              <th>Роль</th>
              <th>Телефон</th>
              <th>Приєднався</th>
              <th>Показники</th>
              <th>Статус</th>
              <th style="width:60px;" class="text-end">Дії</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($team as $member)
              @php
                $isSelf = $member->id === auth()->id();
                $dealsInProgress = ($member->id * 3) % 15 + 1;
                $jsMember = [
                  'id' => $member->id,
                  'name' => $member->name,
                  'email' => $member->email,
                  'phone' => $member->phone,
                  'role' => $member->role,
                  'role_label' => $member->roleLabel(),
                  'role_description' => $member->roleDescription(),
                  'role_badge' => $member->roleBadgeClass(),
                  'department' => $member->department,
                  'hire_date' => optional($member->hire_date)->format('Y-m-d'),
                  'is_active' => $member->is_active,
                  'is_self' => $isSelf,
                  'avatar_url' => $member->avatar_path ? asset($member->avatar_path) : null,
                  'avatar_color' => $member->avatarColor(),
                  'initials' => $member->initials(),
                  'update_url' => route('team.update', $member),
                  'destroy_url' => route('team.destroy', $member),
                  'reset_password_url' => route('team.reset-password', $member),
                  'deals_in_progress' => $dealsInProgress,
                ];
              @endphp
              <tr class="team-row" data-member='@json($jsMember)'>
                <td><x-user-avatar :user="$member" :size="40" /></td>
                <td>
                  <div class="fw-semibold">{{ $member->name }}</div>
                  <div class="small text-muted">{{ $member->email }}</div>
                </td>
                <td><span class="badge {{ $member->roleBadgeClass() }}">{{ $member->roleLabel() }}</span></td>
                <td>{{ $member->phone ?: '—' }}</td>
                <td>{{ optional($member->hire_date)->format('d.m.Y') ?: '—' }}</td>
                <td>{{ $dealsInProgress }} угод в роботі</td>
                <td onclick="event.stopPropagation()">
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input toggle-active" type="checkbox" role="switch" data-id="{{ $member->id }}" @checked($member->is_active) @disabled($isSelf)>
                  </div>
                </td>
                <td class="text-end" onclick="event.stopPropagation()">
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light btn-icon" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item edit-member-btn" href="#"><i class="ti ti-pencil me-2"></i>Редагувати</a></li>
                      <li>
                        <form method="POST" action="{{ route('team.reset-password', $member) }}">
                          @csrf
                          <button type="submit" class="dropdown-item"><i class="ti ti-key me-2"></i>Скинути пароль</button>
                        </form>
                      </li>
                      @unless ($isSelf)
                        <li>
                          <button type="button" class="dropdown-item toggle-active-menu-item" data-id="{{ $member->id }}">
                            <i class="ti ti-{{ $member->is_active ? 'lock' : 'lock-open' }} me-2"></i>{{ $member->is_active ? 'Заблокувати' : 'Активувати' }}
                          </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                          <form method="POST" action="{{ route('team.destroy', $member) }}" class="delete-member-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger"><i class="ti ti-trash me-2"></i>Видалити</button>
                          </form>
                        </li>
                      @endunless
                    </ul>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- GRID VIEW -->
    <div id="teamGridView" class="row g-3 d-none">
      @foreach ($team as $member)
        @php
          $isSelf = $member->id === auth()->id();
          $dealsInProgress = ($member->id * 3) % 15 + 1;
          $jsMember = [
            'id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'phone' => $member->phone,
            'role' => $member->role,
            'role_label' => $member->roleLabel(),
            'role_description' => $member->roleDescription(),
            'role_badge' => $member->roleBadgeClass(),
            'department' => $member->department,
            'hire_date' => optional($member->hire_date)->format('Y-m-d'),
            'is_active' => $member->is_active,
            'is_self' => $isSelf,
            'avatar_url' => $member->avatar_path ? asset($member->avatar_path) : null,
            'avatar_color' => $member->avatarColor(),
            'initials' => $member->initials(),
            'update_url' => route('team.update', $member),
            'destroy_url' => route('team.destroy', $member),
            'reset_password_url' => route('team.reset-password', $member),
            'deals_in_progress' => $dealsInProgress,
          ];
        @endphp
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card team-card h-100" data-member='@json($jsMember)'>
            <div class="card-body text-center position-relative">
              <div class="dropdown position-absolute top-0 end-0 mt-2 me-2" onclick="event.stopPropagation()">
                <button class="btn btn-sm btn-light btn-icon" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item edit-member-btn" href="#"><i class="ti ti-pencil me-2"></i>Редагувати</a></li>
                  <li>
                    <form method="POST" action="{{ route('team.reset-password', $member) }}">
                      @csrf
                      <button type="submit" class="dropdown-item"><i class="ti ti-key me-2"></i>Скинути пароль</button>
                    </form>
                  </li>
                  @unless ($isSelf)
                    <li>
                      <button type="button" class="dropdown-item toggle-active-menu-item" data-id="{{ $member->id }}">
                        <i class="ti ti-{{ $member->is_active ? 'lock' : 'lock-open' }} me-2"></i>{{ $member->is_active ? 'Заблокувати' : 'Активувати' }}
                      </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form method="POST" action="{{ route('team.destroy', $member) }}" class="delete-member-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger"><i class="ti ti-trash me-2"></i>Видалити</button>
                      </form>
                    </li>
                  @endunless
                </ul>
              </div>

              <div class="d-flex justify-content-center mb-3 mt-2">
                <x-user-avatar :user="$member" :size="72" />
              </div>
              <div class="fw-semibold">{{ $member->name }}</div>
              <div class="mb-2"><span class="badge {{ $member->roleBadgeClass() }}">{{ $member->roleLabel() }}</span></div>
              <div class="small text-muted text-truncate">{{ $member->email }}</div>
              <div class="small text-muted">{{ $member->phone ?: '—' }}</div>
              <div class="small mt-3 pt-3 border-top">{{ $dealsInProgress }} угод в роботі</div>
              @if (! $member->is_active)
                <span class="badge bg-danger-subtle text-danger mt-2">Заблоковано</span>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-4 d-flex justify-content-center">
      {{ $team->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
  @endif

  <!-- OFFCANVAS: Add/Edit member -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="memberFormOffcanvas" style="width: 420px;" @if($errors->any()) data-open-on-load="true" @endif>
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title" id="memberFormTitle">{{ old('member_id') ? 'Редагувати співробітника' : 'Додати співробітника' }}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
      <form id="memberForm" method="POST" enctype="multipart/form-data"
        action="{{ old('member_id') ? route('team.update', old('member_id')) : route('team.store') }}"
        data-store-url="{{ route('team.store') }}" class="d-flex flex-column flex-grow-1">
        @csrf
        <input type="hidden" name="_method" id="memberFormMethod" value="{{ old('member_id') ? 'PUT' : 'POST' }}">
        <input type="hidden" name="member_id" id="field_member_id" value="{{ old('member_id') }}">

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="mb-3">
          <label class="form-label">Фото</label>
          <div id="memberDropzone" class="border border-2 border-dashed rounded-3 p-3 text-center position-relative">
            <img id="memberAvatarPreview" src="" class="d-none rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
            <div id="memberDropzonePlaceholder">
              <i class="ti ti-cloud-upload fs-2 text-muted d-block mb-1"></i>
              <p class="small text-muted mb-0">Перетягніть фото сюди або натисніть, щоб обрати</p>
            </div>
            <input type="file" name="avatar" id="memberAvatarInput" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Ім'я та прізвище</label>
          <input type="text" name="name" id="field_member_name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" id="field_member_email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Телефон</label>
          <input type="text" name="phone" id="field_member_phone" class="form-control" placeholder="+380 XX XXX XX XX" value="{{ old('phone') }}">
        </div>

        <div class="mb-1">
          <label class="form-label">Роль</label>
          <x-custom-select
              name="role"
              id="field_member_role"
              :options="\App\Models\User::ROLES"
              :selected="old('role', 'manager')"
          />
        </div>
        <p class="small text-muted mb-3" id="roleDescription">{{ \App\Models\User::ROLE_DESCRIPTIONS['manager'] }}</p>

        <div class="mb-3">
          <label class="form-label">Відділ <span class="text-muted small">(необов'язково)</span></label>
          <x-custom-select
              name="department"
              id="field_member_department"
              :options="\App\Models\User::DEPARTMENTS"
              :selected="old('department')"
              placeholder="Не вказано"
          />
        </div>

        <div class="mb-3">
          <label class="form-label">Дата найму</label>
          <input type="date" name="hire_date" id="field_member_hire_date" class="form-control" value="{{ old('hire_date') }}">
        </div>

        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="field_member_is_active" @checked(old('is_active', true))>
          <label class="form-check-label" for="field_member_is_active">Активний співробітник</label>
        </div>

        <div class="form-check mb-4" id="sendInviteWrapper">
          <input class="form-check-input" type="checkbox" name="send_invite" value="1" id="field_send_invite" checked>
          <label class="form-check-label" for="field_send_invite">Відправити запрошення на email</label>
        </div>

        <div class="mt-auto d-flex gap-2 pt-3 border-top">
          <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="offcanvas">Скасувати</button>
          <button type="submit" class="btn btn-primary w-50" id="memberFormSubmitBtn">Зберегти</button>
        </div>
      </form>
    </div>
  </div>

  <!-- OFFCANVAS: Member details -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="memberDetailsOffcanvas" style="width: 420px;">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title">Деталі співробітника</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <div class="text-center mb-4">
        <div class="d-flex justify-content-center mb-3" id="detailsAvatarWrapper"></div>
        <div class="fw-semibold fs-5" id="detailsName"></div>
        <span class="badge" id="detailsRoleBadge"></span>
      </div>

      <div class="mb-4">
        <div class="d-flex align-items-center gap-2 small mb-2"><i class="ti ti-mail text-muted"></i><span id="detailsEmail"></span></div>
        <div class="d-flex align-items-center gap-2 small"><i class="ti ti-phone text-muted"></i><span id="detailsPhone"></span></div>
      </div>

      <div class="row g-2 mb-4">
        <div class="col-6">
          <div class="card p-3 text-center h-100">
            <div class="fs-4 fw-bold" id="detailsClients"></div>
            <div class="small text-muted">Клієнтів у роботі</div>
          </div>
        </div>
        <div class="col-6">
          <div class="card p-3 text-center h-100">
            <div class="fs-4 fw-bold" id="detailsDeals"></div>
            <div class="small text-muted">Активних угод</div>
          </div>
        </div>
        <div class="col-6">
          <div class="card p-3 text-center h-100">
            <div class="fs-4 fw-bold" id="detailsClosed"></div>
            <div class="small text-muted">Закрито за місяць</div>
          </div>
        </div>
        <div class="col-6">
          <div class="card p-3 text-center h-100">
            <div class="fs-4 fw-bold" id="detailsRevenue"></div>
            <div class="small text-muted">Продажів за місяць</div>
          </div>
        </div>
      </div>

      <div class="d-flex gap-2 mb-4">
        <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1" id="detailsEditBtn"><i class="ti ti-pencil me-1"></i>Редагувати</button>
        <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1" id="detailsResetPasswordBtn"><i class="ti ti-key me-1"></i>Пароль</button>
        <button type="button" class="btn btn-outline-danger btn-sm flex-grow-1" id="detailsBlockBtn"><i class="ti ti-lock me-1"></i>Блок</button>
      </div>

      <h6 class="text-muted small text-uppercase mb-3">Останні дії</h6>
      <ul class="list-unstyled small d-flex flex-column gap-3">
        <li class="d-flex gap-2">
          <i class="ti ti-circle-check text-success mt-1"></i>
          <div><div>Закрив угоду з клієнтом «Альфа Груп»</div><div class="text-muted">2 дні тому</div></div>
        </li>
        <li class="d-flex gap-2">
          <i class="ti ti-user-plus text-primary mt-1"></i>
          <div><div>Додав нового клієнта «Бета Сервіс»</div><div class="text-muted">4 дні тому</div></div>
        </li>
        <li class="d-flex gap-2">
          <i class="ti ti-edit text-secondary mt-1"></i>
          <div><div>Оновив умови угоди</div><div class="text-muted">1 тиждень тому</div></div>
        </li>
      </ul>
    </div>
  </div>

  <form id="reset-password-details-form" method="POST" class="d-none">@csrf</form>

@endsection
