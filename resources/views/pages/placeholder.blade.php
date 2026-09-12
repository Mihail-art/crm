@extends('layouts.app')

@section('title', $title)

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="mb-6">
        <h1 class="fs-3 mb-1">{{ $title }}</h1>
        <p class="text-muted">Цей розділ у розробці.</p>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body p-5 text-center">
          <div class="icon-shape icon-xl bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4">
            <i class="ti {{ $icon }} fs-1"></i>
          </div>
          <h3 class="h5 mb-2">Розділ "{{ $title }}" скоро з'явиться</h3>
          <p class="text-muted mb-0">Функціонал цієї сторінки ще розробляється.</p>
        </div>
      </div>
    </div>
  </div>
@endsection
