@extends('layouts.app')

@section('title', 'Товари')

@push('scripts')
  @vite(['resources/js/products.js'])
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
      <h1 class="fs-3 mb-0">Товари</h1>
    </div>
    <div class="col-auto">
      <button type="button" class="btn btn-primary" id="addProductBtn">
        <i class="ti ti-plus me-1"></i> Додати товар
      </button>
    </div>
  </div>

  <form method="GET" action="{{ route('products') }}" id="filtersForm" class="row g-2 align-items-center mb-4">
    <div class="col-12 col-md-4">
      <div class="input-group">
        <span class="input-group-text bg-body-tertiary border-end-0"><i class="ti ti-search"></i></span>
        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Пошук за назвою або SKU" value="{{ $filters['search'] ?? '' }}">
      </div>
    </div>
    <div class="col-6 col-md-2">
      <x-custom-select
          name="category"
          :options="$categories->pluck('name', 'id')"
          :selected="$filters['category'] ?? ''"
          placeholder="Усі категорії"
          onchange="this.form.submit()"
      />
    </div>
    <div class="col-6 col-md-2">
      <x-custom-select
          name="stock"
          :options="['in' => 'В наявності', 'low' => 'Закінчується', 'out' => 'Немає']"
          :selected="$filters['stock'] ?? ''"
          placeholder="Наявність: усі"
          onchange="this.form.submit()"
      />
    </div>
    <div class="col-6 col-md-2">
      <x-custom-select
          name="sort"
          :options="['name' => 'За назвою', 'price' => 'За ціною', 'stock' => 'За залишком']"
          :selected="$filters['sort'] ?? 'name'"
          onchange="this.form.submit()"
      />
    </div>
    <div class="col-6 col-md-2 d-flex justify-content-md-end">
      <div class="btn-group" role="group" aria-label="Вигляд">
        <button type="button" class="btn btn-outline-secondary" id="viewTableBtn" title="Таблиця"><i class="ti ti-list"></i></button>
        <button type="button" class="btn btn-outline-secondary" id="viewGridBtn" title="Картки"><i class="ti ti-layout-grid"></i></button>
      </div>
    </div>
  </form>

  @if ($products->isEmpty())
    <div class="card">
      <div class="card-body py-5 text-center">
        <div class="icon-shape icon-xxl bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4">
          <i class="ti ti-package fs-1"></i>
        </div>
        @if (array_filter($filters))
          <h3 class="h5 mb-2">Нічого не знайдено</h3>
          <p class="text-muted mb-4">Спробуйте змінити параметри пошуку або фільтри.</p>
          <a href="{{ route('products') }}" class="btn btn-outline-secondary">Скинути фільтри</a>
        @else
          <h3 class="h5 mb-2">Товарів поки немає</h3>
          <p class="text-muted mb-4">Додайте перший товар, щоб почати наповнення каталогу.</p>
          <button type="button" class="btn btn-primary" id="emptyAddProductBtn"><i class="ti ti-plus me-1"></i>Додати перший товар</button>
        @endif
      </div>
    </div>
  @else

    <!-- TABLE VIEW -->
    <div id="productsTableView" class="card">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th style="width:56px;"></th>
              <th>Назва</th>
              <th>Категорія</th>
              <th>Ціна</th>
              <th>Залишок</th>
              <th>Статус</th>
              <th style="width:80px;" class="text-end">Дії</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($products as $product)
              @php
                $stockStatus = $product->stockStatus();
                $jsProduct = [
                  'id' => $product->id,
                  'name' => $product->name,
                  'category_id' => $product->category_id,
                  'description' => $product->description,
                  'price' => $product->price,
                  'wholesale_price' => $product->wholesale_price,
                  'unit' => $product->unit,
                  'sku' => $product->sku,
                  'stock_quantity' => $product->stock_quantity,
                  'min_stock_alert' => $product->min_stock_alert,
                  'is_active' => $product->is_active,
                  'image_url' => $product->image_path ? asset($product->image_path) : null,
                  'update_url' => route('products.update', $product),
                ];
              @endphp
              <tr class="product-row" data-product='@json($jsProduct)'>
                <td>
                  @if ($product->image_path)
                    <img src="{{ asset($product->image_path) }}" width="40" height="40" class="rounded object-fit-cover" alt="">
                  @else
                    <span class="icon-shape rounded bg-body-secondary text-muted" style="width:40px;height:40px;"><i class="ti ti-photo"></i></span>
                  @endif
                </td>
                <td>
                  <div class="fw-semibold">{{ $product->name }}</div>
                  @if ($product->sku)<div class="small text-muted">SKU: {{ $product->sku }}</div>@endif
                </td>
                <td><span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $product->category->name }}</span></td>
                <td>
                  <div>{{ number_format($product->price, 0, ',', ' ') }} ₴</div>
                  @if ($product->wholesale_price)
                    <div class="small text-muted">опт: {{ number_format($product->wholesale_price, 0, ',', ' ') }} ₴</div>
                  @endif
                </td>
                <td>
                  <span class="stock-dot stock-dot-{{ $stockStatus }}"></span>{{ $product->stock_quantity }} {{ $product->unit }}
                </td>
                <td onclick="event.stopPropagation()">
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input toggle-active" type="checkbox" role="switch" data-id="{{ $product->id }}" @checked($product->is_active)>
                  </div>
                </td>
                <td class="text-end" onclick="event.stopPropagation()">
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light btn-icon" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item edit-product-btn" href="#"><i class="ti ti-pencil me-2"></i>Редагувати</a></li>
                      <li>
                        <form method="POST" action="{{ route('products.duplicate', $product) }}">
                          @csrf
                          <button type="submit" class="dropdown-item"><i class="ti ti-copy me-2"></i>Дублювати</button>
                        </form>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" class="delete-product-form">
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

    <!-- GRID VIEW -->
    <div id="productsGridView" class="row g-3 d-none">
      @foreach ($products as $product)
        @php
          $stockStatus = $product->stockStatus();
          $jsProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'category_id' => $product->category_id,
            'description' => $product->description,
            'price' => $product->price,
            'wholesale_price' => $product->wholesale_price,
            'unit' => $product->unit,
            'sku' => $product->sku,
            'stock_quantity' => $product->stock_quantity,
            'min_stock_alert' => $product->min_stock_alert,
            'is_active' => $product->is_active,
            'image_url' => $product->image_path ? asset($product->image_path) : null,
            'update_url' => route('products.update', $product),
          ];
        @endphp
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card h-100 product-card" data-product='@json($jsProduct)'>
            <div class="ratio ratio-1x1 bg-body-secondary rounded-top overflow-hidden">
              @if ($product->image_path)
                <img src="{{ asset($product->image_path) }}" class="w-100 h-100 object-fit-cover" alt="">
              @else
                <div class="d-flex align-items-center justify-content-center text-muted"><i class="ti ti-photo fs-1"></i></div>
              @endif
            </div>
            <div class="card-body">
              <div class="fw-semibold text-truncate">{{ $product->name }}</div>
              <div class="text-primary fw-bold">{{ number_format($product->price, 0, ',', ' ') }} ₴</div>
              <div class="small text-muted mt-2"><span class="stock-dot stock-dot-{{ $stockStatus }}"></span>{{ $product->stock_quantity }} {{ $product->unit }}</div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-4 d-flex justify-content-center">
      {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
  @endif

  <!-- OFFCANVAS: Add/Edit product -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="productOffcanvas" style="width: 420px;" @if($errors->any()) data-open-on-load="true" @endif>
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title" id="offcanvasTitle">{{ old('product_id') ? 'Редагувати товар' : 'Додати товар' }}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
      <form id="productForm" method="POST" enctype="multipart/form-data"
        action="{{ old('product_id') ? route('products.update', old('product_id')) : route('products.store') }}"
        data-store-url="{{ route('products.store') }}" class="d-flex flex-column flex-grow-1">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="{{ old('product_id') ? 'PUT' : 'POST' }}">
        <input type="hidden" name="product_id" id="field_product_id" value="{{ old('product_id') }}">

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
          <label class="form-label">Фото товару</label>
          <div id="dropzone" class="border border-2 border-dashed rounded-3 p-3 text-center position-relative">
            <img id="imagePreview" src="" class="d-none rounded mb-2" style="max-height: 120px;">
            <div id="dropzonePlaceholder">
              <i class="ti ti-cloud-upload fs-2 text-muted d-block mb-1"></i>
              <p class="small text-muted mb-0">Перетягніть файл сюди або натисніть, щоб обрати</p>
            </div>
            <input type="file" name="image" id="imageInput" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Назва товару</label>
          <input type="text" name="name" id="field_name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Категорія</label>
          <x-custom-select
              name="category_id"
              id="field_category_id"
              :options="$categories->pluck('name', 'id')"
              :selected="old('category_id')"
              :extra-options="['__new__' => '+ Додати нову категорію']"
          />
          <input type="text" name="new_category" id="field_new_category" class="form-control mt-2 d-none" value="{{ old('new_category') }}" placeholder="Назва нової категорії">
        </div>

        <div class="mb-3">
          <label class="form-label">Опис</label>
          <textarea name="description" id="field_description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Ціна роздрібна</label>
            <input type="number" step="0.01" min="0" name="price" id="field_price" class="form-control" value="{{ old('price') }}" required>
          </div>
          <div class="col-6">
            <label class="form-label">Ціна опт <span class="text-muted small">(необов'язково)</span></label>
            <input type="number" step="0.01" min="0" name="wholesale_price" id="field_wholesale_price" class="form-control" value="{{ old('wholesale_price') }}">
          </div>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Одиниця виміру</label>
            <x-custom-select
                name="unit"
                id="field_unit"
                :options="['шт' => 'шт', 'кг' => 'кг', 'м' => 'м', 'упаковка' => 'упаковка']"
                :selected="old('unit', 'шт')"
            />
          </div>
          <div class="col-6">
            <label class="form-label">Артикул / SKU</label>
            <input type="text" name="sku" id="field_sku" class="form-control" value="{{ old('sku') }}">
          </div>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Залишок на складі</label>
            <input type="number" min="0" name="stock_quantity" id="field_stock_quantity" class="form-control" value="{{ old('stock_quantity') }}" required>
          </div>
          <div class="col-6">
            <label class="form-label">Мін. залишок</label>
            <input type="number" min="0" name="min_stock_alert" id="field_min_stock_alert" class="form-control" value="{{ old('min_stock_alert', 5) }}">
          </div>
        </div>

        <div class="form-check form-switch mb-4">
          <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="field_is_active" @checked(old('is_active', true))>
          <label class="form-check-label" for="field_is_active">Активний товар</label>
        </div>

        <div class="mt-auto d-flex gap-2 pt-3 border-top">
          <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="offcanvas">Скасувати</button>
          <button type="submit" class="btn btn-primary w-50">Зберегти</button>
        </div>
      </form>
    </div>
  </div>

@endsection
