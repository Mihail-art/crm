@extends('layouts.app')

@section('title', 'Дашборд')

@section('content')
  <div class="row ">
    <div class="col-12">
      <div class="mb-6">
        <h1 class="fs-3 mb-1">Дашборд</h1>
        <p>Вітаємо, {{ auth()->user()->name }}!</p>
      </div>
    </div>
  </div>
  <div class="row g-3 mb-3">
    <div class="col-lg-3 col-12">

      <div class="card p-4  bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-2">

        <div class="d-flex gap-3 ">
          <div class="icon-shape icon-md bg-primary text-white rounded-2">
            <i class="ti ti-report-analytics fs-4"></i>
          </div>
          <div>
            <h2 class="mb-3 fs-6">Загальні продажі</h2>
            <h3 class="fw-bold mb-0">$25,000</h3>
            <p class="text-primary mb-0 small">+5% з минулого місяця</p>
          </div>
        </div>
      </div>


    </div>
    <div class="col-lg-3 col-12">

      <div class="card p-4  bg-success bg-opacity-10 border border-success border-opacity-25 rounded-2">

        <div class="d-flex gap-3 ">
          <div class="icon-shape icon-md bg-success text-white rounded-2">
            <i class="ti ti-repeat fs-4"></i>
          </div>
          <div>
            <h2 class="mb-3 fs-6">Загальні закупівлі</h2>
            <h3 class="fw-bold mb-0">$18,000</h3>
            <p class="text-success mb-0 small">+22% з минулого місяця</p>
          </div>
        </div>
      </div>


    </div>
    <div class="col-lg-3 col-12">

      <div class="card p-4  bg-info bg-opacity-10 border border-info border-opacity-25 rounded-2">

        <div class="d-flex gap-3 ">
          <div class="icon-shape icon-md bg-info text-white rounded-2">
            <i class="ti ti-currency-dollar fs-4"></i>
          </div>
          <div>
            <h2 class="mb-3 fs-6">Загальні витрати</h2>
            <h3 class="fw-bold mb-0">$9,000</h3>
            <p class="text-info mb-0 small">+10% з минулого місяця</p>
          </div>
        </div>
      </div>


    </div>
    <div class="col-lg-3 col-12">

      <div class="card p-4  bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-2">

        <div class="d-flex gap-3 ">
          <div class="icon-shape icon-md bg-warning text-white rounded-2">
            <i class="ti ti-notes fs-4"></i>
          </div>
          <div>
            <h2 class="mb-3 fs-6">До сплати</h2>
            <h3 class="fw-bold mb-0">$25,000</h3>
            <p class="text-warning mb-0 small">+35% з минулого місяця</p>
          </div>
        </div>
      </div>


    </div>

  </div>
  <div class="row g-3 mb-3">
    <div class="col-lg-4 col-12">
      <div class="card">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between border-bottom pb-5 mb-3">
            <div>
              <h3 class="fw-bold h4">$25,458</h3>
              <span>Загальний прибуток</span>
            </div>
            <div>
              <i class="ti ti-layers-subtract fs-1 text-primary"></i>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center small">
            <div class="text-muted"><span class="text-success">+35%</span> порівняно з мин. місяцем</div>
            <div><a href="#" class="link-primary text-decoration-underline">Переглянути</a></div>
          </div>
        </div>
      </div>

    </div>
    <div class="col-lg-4 col-12">
      <div class="card">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between border-bottom pb-5 mb-3">
            <div>
              <h3 class="fw-bold h4">$45,458</h3>
              <span>Повернення платежів</span>
            </div>
            <div>
              <i class="ti ti-credit-card fs-1 text-danger"></i>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center small">
            <div class="text-muted"><span class="text-danger">-20%</span> порівняно з мин. місяцем</div>
             <div><a href="#" class="link-primary text-decoration-underline">Переглянути</a></div>
          </div>
        </div>
      </div>

    </div>
    <div class="col-lg-4 col-12">
      <div class="card">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between border-bottom pb-5 mb-3">
            <div>
              <h3 class="fw-bold h4">$34,458</h3>
              <span>Загальні витрати</span>
            </div>
            <div>
              <i class="ti ti-cash-banknote fs-1 text-warning"></i>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center small">
            <div class="text-muted"><span class="text-warning">-20%</span> порівняно з мин. місяцем</div>
            <div><a href="#" class="link-primary text-decoration-underline">Переглянути</a></div>
          </div>
        </div>
      </div>

    </div>

  </div>
  <div class="row g-3 mb-3">
    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-transparent px-4 py-3">
          <h3 class="h5 mb-0">Продажі vs Закупівлі</h3>
          <div style="min-width: 140px;">
            <x-custom-select
                :options="['year' => 'Цей рік', 'month' => 'Цей місяць', 'week' => 'Цей тиждень']"
                selected="year"
            />
          </div>
        </div>
        <div class="card-body p-4">

          <div id="salesPurchaseChart"></div>
        </div>
      </div>
    </div>


    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-transparent px-4 py-3">
          <h3 class="h5 mb-0">Загальна інформація</h3>
          <div style="min-width: 160px;">
            <x-custom-select
                :options="['6months' => 'Останні 6 місяців', 'month' => 'Цей місяць', 'week' => 'Цей тиждень']"
                selected="6months"
            />
          </div>
        </div>
        <div class="card-body p-4">
          <h3 class="h6">Огляд клієнтів</h3>
          <div class="row align-items-center">
            <div class="col-sm-6">
              <div id="customerChart">

              </div>
            </div>
            <div class="col-sm-6">
              <div class="row">
                <div class="col-6 border-end">
                  <div class="text-center ">
                    <h2 class="mb-1">5.5K</h2>
                    <p class="text-success mb-2">Нові</p>
                    <span class="badge bg-success"><i class="ti ti-arrow-up-left me-1"></i>25%</span>
                  </div>
                </div>
                <div class="col-6">
                  <div class="text-center">
                    <h2 class="mb-1">3.5K</h2>
                    <p class="text-warning mb-2">Повторні</p>
                    <span class="badge bg-success badge-xs d-inline-flex align-items-center"><i
                        class="ti ti-arrow-up-left me-1"></i>21%</span>
                  </div>
                </div>
              </div>
            </div>


          </div>
          <div class="row text-center border-top mt-4 pt-4">
            <div class="col-4 border-end">
              <h3 class="fw-bold mb-2">6987</h3>
              <small class="text-secondary">Постачальники</small>
            </div>
            <div class="col-4 border-end">
              <h3 class="fw-bold mb-2">4896</h3>
              <small class="text-secondary">Клієнти</small>
            </div>
            <div class="col-4">
              <h3 class="fw-bold mb-2">487</h3>
              <small class="text-secondary">Замовлення</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
