@php
    $user = filament()->auth()->user();
@endphp

<div class="bg-red-500">
    
    @if($user->isSuperAdmin())

        <div class="text-xs text-gray-500">
            SUPERADMIN - {{ $user->name}}
        </div>

        <div class="font-bold">
            Portal Rumah Sakit
        </div>

    @else

        <div class="text-xs text-gray-500">
            ADMIN - {{ $user->name}}
        </div>

        <div class="font-bold">
            {{ $user->rumahSakit?->nama }}
        </div>

    @endif

</div>