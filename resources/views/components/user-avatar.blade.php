@props(['user', 'size' => 40])

@if ($user->avatar_path)
    <img src="{{ asset($user->avatar_path) }}" width="{{ $size }}" height="{{ $size }}" class="rounded-circle object-fit-cover" alt="">
@else
    <span class="avatar-initials" style="width:{{ $size }}px;height:{{ $size }}px;background-color:{{ $user->avatarColor() }};font-size:{{ round($size / 2.5) }}px;">{{ $user->initials() }}</span>
@endif
