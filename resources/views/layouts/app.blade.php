<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f7f7f3">
    <title>@yield('title', 'Pedidos') | Tiendita Lupita</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased">
    <div class="mx-auto min-h-screen max-w-2xl px-4 pb-28 sm:px-6">
        <header class="sticky top-0 z-20 -mx-4 border-b border-stone-200/80 bg-[var(--canvas)]/95 px-4 pb-3 pt-4 backdrop-blur sm:-mx-6 sm:px-6">
            <a href="{{ route('orders.index') }}" class="flex items-center gap-3" aria-label="Tiendita Lupita, inicio">
                <span class="grid size-11 place-items-center rounded-2xl bg-[var(--brand)] text-lg font-black text-white">TL</span>
                <span>
                    <span class="block text-lg font-extrabold leading-tight tracking-normal">Tiendita Lupita</span>
                    <span class="block text-xs font-medium text-stone-500">Pedidos SHEIN, sin complicaciones</span>
                </span>
            </a>
        </header>

        @if (session('success'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                <p class="font-bold">Revisa estos datos:</p>
                <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <main class="py-5">
            @yield('content')
        </main>
    </div>

    <nav class="safe-bottom fixed inset-x-0 bottom-0 z-30 border-t border-stone-200 bg-white/95 px-2 pt-2 shadow-[0_-8px_24px_rgba(36,33,31,0.07)] backdrop-blur" aria-label="Navegación principal">
        <div class="mx-auto grid max-w-xl grid-cols-5 items-end gap-1 pb-1">
            <a href="{{ route('orders.index') }}" @class(['flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl text-[11px] font-semibold', 'text-[var(--brand)]' => request()->routeIs('orders.index'), 'text-stone-500' => !request()->routeIs('orders.index')])>
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.5 7.8 8.5 4.4 8.5-4.4M12 12.5V21"/></svg>
                Pedidos
            </a>
            <a href="{{ route('debts.index') }}" @class(['flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl text-[11px] font-semibold', 'text-[var(--brand)]' => request()->routeIs('debts.index'), 'text-stone-500' => !request()->routeIs('debts.index')])>
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m4-14H9.5a3 3 0 0 0 0 6h5a3 3 0 0 1 0 6H7"/></svg>
                Deudas
            </a>
            <a href="{{ route('orders.create') }}" class="-mt-5 flex min-h-16 flex-col items-center justify-center gap-1 rounded-2xl bg-[var(--brand)] px-2 text-[11px] font-bold text-white shadow-lg shadow-rose-900/20 active:scale-95">
                <span class="grid size-8 place-items-center rounded-full bg-white/15"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg></span>
                Nuevo
            </a>
            <a href="{{ route('clients.index') }}" @class(['flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl text-[11px] font-semibold', 'text-[var(--brand)]' => request()->routeIs('clients.*'), 'text-stone-500' => !request()->routeIs('clients.*')])>
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 4 18.5V20m6-10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm6-6.5a3.5 3.5 0 0 1 0 6.8m1.5 3.2a4.5 4.5 0 0 1 3.5 4.4V20"/></svg>
                Clientes
            </a>
            <a href="{{ route('orders.history') }}" @class(['flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl text-[11px] font-semibold', 'text-[var(--brand)]' => request()->routeIs('orders.history'), 'text-stone-500' => !request()->routeIs('orders.history')])>
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 2.64-6.36L3 8m0-5v5h5m4-1v5l3 2"/></svg>
                Historial
            </a>
        </div>
    </nav>
</body>
</html>
