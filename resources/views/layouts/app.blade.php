<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f7f7f3">
    <title>@yield('title', 'Pedidos') | Tiendita Lupita</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    <script>
        try {
            document.documentElement.dataset.theme = localStorage.getItem('tiendita-theme') || 'light';
        } catch {
            document.documentElement.dataset.theme = 'light';
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen font-sans antialiased">
    <div class="mx-auto min-h-screen max-w-7xl px-4 pb-28 sm:px-6 lg:pb-8">
        <header
            class="sticky top-0 z-20 -mx-4 border-b border-stone-200/80 bg-[var(--canvas)]/95 px-4 pb-3 pt-4 backdrop-blur sm:-mx-6 sm:px-6 lg:mx-0 lg:px-0">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('admin') }}" class="flex items-center gap-3" aria-label="Tiendita Lupita, panel">
                    <img src="{{ asset('favicon.svg') }}" alt="" class="size-10 shrink-0 rounded-xl shadow-sm">
                    <span>
                        <span class="block text-lg font-extrabold leading-tight tracking-normal">Tiendita Lupita</span>
                        <span class="block text-xs font-medium text-stone-500">Administración</span>
                    </span>
                </a>
                <div class="flex items-center gap-2">
                    <button id="theme-toggle" type="button"
                        class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 text-xs font-bold text-stone-700 shadow-sm transition hover:border-stone-300"
                        aria-label="Cambiar a modo oscuro" title="Cambiar a modo oscuro">
                        <svg data-theme-icon="dark" viewBox="0 0 24 24" class="size-4" fill="none"
                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.2 15.1A8.6 8.6 0 0 1 8.9 3.8 8.6 8.6 0 1 0 20.2 15.1Z" />
                        </svg>
                        <svg data-theme-icon="light" viewBox="0 0 24 24" class="hidden size-4" fill="none"
                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="4" />
                            <path stroke-linecap="round"
                                d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" />
                        </svg>
                        <span data-theme-label>Oscuro</span>
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="min-h-10 rounded-xl px-3 text-xs font-bold text-stone-600 transition hover:bg-white">Salir</button>
                    </form>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"
                role="status">{{ session('success') }}</div>
        @endif
        @if (session('whatsapp_url') && request()->boolean('whatsapp'))
            <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
                role="status">
                <p class="font-semibold">Abriendo WhatsApp con el resumen actualizado.</p>
                <a href="{{ session('whatsapp_url') }}" target="_blank" rel="noopener"
                    class="mt-1 inline-block font-bold underline">Si no se abre, entra al resumen aquí</a>
            </div>
            <script>
                const paymentNotification = {
                    type: 'tiendita:payment-recorded',
                    source: window.tienditaPaymentTabId,
                };

                if ('BroadcastChannel' in window) {
                    const paymentChannel = new BroadcastChannel('tiendita-payments');
                    paymentChannel.postMessage(paymentNotification);
                    paymentChannel.close();
                } else if (window.opener && !window.opener.closed) {
                    window.opener.postMessage(paymentNotification, window.location.origin);
                }

                window.location.replace(@json(session('whatsapp_url')));
            </script>
        @endif
        @if ($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
                role="alert">
                <p class="font-bold">Revisa estos datos:</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main class="py-5 lg:ml-64 lg:py-8">
            @yield('content')
        </main>
    </div>

    <script>
        window.tienditaPaymentTabId = `${Date.now()}-${Math.random()}`;

        if ('BroadcastChannel' in window) {
            const paymentChannel = new BroadcastChannel('tiendita-payments');
            paymentChannel.addEventListener('message', (event) => {
                if (event.data?.type === 'tiendita:payment-recorded' && event.data.source !== window
                    .tienditaPaymentTabId) {
                    window.location.reload();
                }
            });
        }

        window.addEventListener('message', (event) => {
            if (event.origin === window.location.origin && event.data?.type === 'tiendita:payment-recorded') {
                window.location.reload();
            }
        });
    </script>

    <nav class="safe-bottom fixed inset-x-0 bottom-0 z-30 border-t border-stone-200 bg-white/95 px-2 pt-2 shadow-[0_-8px_24px_rgba(36,33,31,0.07)] backdrop-blur lg:inset-y-0 lg:left-0 lg:right-auto lg:w-60 lg:border-r lg:border-t-0 lg:px-4 lg:pt-24 lg:shadow-[8px_0_24px_rgba(36,33,31,0.04)]"
        aria-label="Navegación principal">
        <div
            class="mx-auto grid max-w-xl grid-cols-5 items-end gap-1 pb-1 lg:max-w-none lg:grid-cols-1 lg:items-stretch lg:gap-2">
            <a href="{{ route('orders.index') }}" @class([
                'flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl text-[11px] font-semibold transition-colors lg:flex-row lg:justify-start lg:gap-3 lg:px-3 lg:text-sm',
                'text-[var(--brand)] bg-rose-50 lg:bg-rose-50/70' => request()->routeIs(
                    'admin',
                    'dashboard',
                    'orders.index',
                    'orders.create',
                    'orders.history'),
                'text-stone-500 hover:bg-stone-50' => !request()->routeIs(
                    'admin',
                    'dashboard',
                    'orders.index',
                    'orders.create',
                    'orders.history'),
            ])>
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="m3.5 7.8 8.5 4.4 8.5-4.4M12 12.5V21" />
                </svg>
                Pedidos
            </a>
            <a href="{{ route('debts.index') }}" @class([
                'flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl text-[11px] font-semibold transition-colors lg:flex-row lg:justify-start lg:gap-3 lg:px-3 lg:text-sm',
                'text-[var(--brand)] bg-rose-50 lg:bg-rose-50/70' => request()->routeIs(
                    'debts.index'),
                'text-stone-500 hover:bg-stone-50' => !request()->routeIs('debts.index'),
            ])>
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 3v18m4-14H9.5a3 3 0 0 0 0 6h5a3 3 0 0 1 0 6H7" />
                </svg>
                Deudas
            </a>
            <a href="{{ route('orders.create') }}" aria-label="Agregar pedido" @class([
                '-mt-5 flex min-h-16 flex-col items-center justify-center gap-1 rounded-2xl bg-[var(--brand)] px-2 text-[10px] font-bold text-white shadow-lg shadow-rose-900/20 active:scale-95 lg:mt-0 lg:min-h-14 lg:flex-row lg:justify-start lg:gap-3 lg:rounded-xl lg:px-3 lg:text-sm lg:shadow-none',
                'ring-2 ring-white' => request()->routeIs('orders.create'),
            ])>
                <span class="grid size-8 place-items-center rounded-full bg-white/15 lg:size-5"><svg viewBox="0 0 24 24"
                        class="size-6 lg:size-5" fill="none" stroke="currentColor" stroke-width="1.8"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                    </svg></span>
                Agregar
            </a>
            <a href="{{ route('clients.index') }}" @class([
                'flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl text-[10px] font-semibold transition-colors lg:flex-row lg:justify-start lg:gap-3 lg:px-3 lg:text-sm',
                'text-[var(--brand)] bg-rose-50 lg:bg-rose-50/70' => request()->routeIs(
                    'clients.*'),
                'text-stone-500 hover:bg-stone-50' => !request()->routeIs('clients.*'),
            ])>
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 4 18.5V20m6-10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm6-6.5a3.5 3.5 0 0 1 0 6.8m1.5 3.2a4.5 4.5 0 0 1 3.5 4.4V20" />
                </svg>
                Clientes
            </a>
            <a href="{{ route('store-debts.index') }}" @class([
                'flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl text-[10px] font-semibold transition-colors lg:flex-row lg:justify-start lg:gap-3 lg:px-3 lg:text-sm',
                'text-[var(--brand)] bg-rose-50 lg:bg-rose-50/70' => request()->routeIs(
                    'store-debts.*'),
                'text-stone-500 hover:bg-stone-50' => !request()->routeIs('store-debts.*'),
            ])>
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 8h18l-1.5 12h-15L3 8Zm4 0 5-5 5 5M8 12v4m4-4v4m4-4v4" />
                </svg>
                Fiado
            </a>
        </div>
    </nav>
</body>

</html>
