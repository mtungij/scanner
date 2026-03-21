<x-slot:title>
    {{ $title ?? config('app.name') }}
</x-slot:title>

<x-layouts.base>
    <x-layouts.partials.nav />

    <div class="mx-auto mt-20 w-full max-w-3xl px-4 sm:mt-24 sm:px-6 lg:px-8">
        {{ $slot }}
    </div>
</x-layouts.base>
