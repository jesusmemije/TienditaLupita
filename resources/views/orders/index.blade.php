@extends('layouts.app')

@section('title', 'Pedidos activos')

@section('content')
    <div x-data="liveSearch()" x-init="init($el)">
        <div class="mb-5 flex items-end justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand)]">Tu tienda, al día</p>
                <h1 class="mt-1 text-2xl font-extrabold">Pedidos activos</h1>
                <p class="mt-1 text-sm text-stone-500"
                    x-text="visibleCount() + ' ' + (visibleCount() === 1 ? 'pedido por entregar' : 'pedidos por entregar')">
                    {{ $orders->count() }} {{ $orders->count() === 1 ? 'pedido por entregar' : 'pedidos por entregar' }}</p>
            </div>
            <a href="{{ route('orders.create') }}"
                class="grid size-12 shrink-0 place-items-center rounded-2xl bg-[var(--brand)] text-2xl font-light text-white shadow-md"
                aria-label="Crear pedido">+</a>
        </div>

        <section aria-label="Resumen de ventas" class="mb-5 grid grid-cols-2 gap-3 xl:grid-cols-4">
            <article class="min-w-0 rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-stone-500">Ventas entregadas · este mes</p>
                <p class="mt-2 truncate text-xl font-extrabold tabular-nums sm:text-2xl">
                    ${{ number_format($salesThisMonth, 2) }}</p>
            </article>
            <article class="min-w-0 rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-stone-500">Cobrado · este mes</p>
                <p class="mt-2 truncate text-xl font-extrabold tabular-nums text-emerald-700 sm:text-2xl">
                    ${{ number_format($collectedThisMonth, 2) }}</p>
            </article>
            <article class="min-w-0 rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-stone-500">Saldo por cobrar</p>
                <p class="mt-2 truncate text-xl font-extrabold tabular-nums text-[var(--brand-dark)] sm:text-2xl">
                    ${{ number_format($currentDue, 2) }}</p>
            </article>
            <article class="min-w-0 rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-stone-500">Pedidos por entregar</p>
                <p class="mt-2 text-xl font-extrabold tabular-nums sm:text-2xl">{{ $pendingOrderCount }}</p>
            </article>
        </section>

        @php
            $topClientMaxSales = (float) ($topClients->first()->delivered_sales ?? 0);
        @endphp
        <section aria-label="Clientas que más compran"
            class="mb-6 rounded-lg border border-stone-200 bg-white p-4 shadow-sm sm:p-5">
            <details class="group">
                <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3">
                    <span>
                        <span class="block text-base font-extrabold">Clientas que más compran</span>
                        <span class="mt-1 block text-xs text-stone-500">Top 10 · pedidos entregados</span>
                    </span>
                    <svg viewBox="0 0 24 24"
                        class="size-5 shrink-0 text-stone-500 transition-transform group-open:rotate-180" fill="none"
                        stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                    </svg>
                </summary>
                <div class="mt-3 border-t border-stone-100 pt-1">
                    <p class="py-2 text-xs text-stone-500">Ordenadas por pagos recibidos, sin incluir saldos pendientes.</p>
                    @if ($topClients->isNotEmpty())
                        <ol class="divide-y divide-stone-100">
                            @foreach ($topClients as $client)
                                <li class="flex items-center gap-3 py-3">
                                    <span
                                        @class([
                                            'grid size-9 shrink-0 place-items-center rounded-lg text-xs font-extrabold',
                                            'bg-[var(--brand)] text-white' => $loop->first,
                                            'bg-stone-100 text-stone-500' => !$loop->first,
                                        ])>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                                            <p class="truncate text-sm font-bold">{{ $client->name }}</p>
                                            <p class="shrink-0 text-xs text-stone-500">{{ $client->delivered_order_count }}
                                                {{ $client->delivered_order_count === 1 ? 'pedido' : 'pedidos' }}</p>
                                        </div>
                                        @if ($client->internal_name)
                                            <p class="truncate text-xs text-stone-500">{{ $client->internal_name }}</p>
                                        @endif
                                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-stone-100"
                                            role="presentation">
                                            <span class="block h-full rounded-full bg-[var(--brand)]"
                                                style="width: {{ $topClientMaxSales > 0 ? min(100, ((float) $client->delivered_sales / $topClientMaxSales) * 100) : 0 }}%"></span>
                                        </div>
                                    </div>
                                    <span
                                        class="shrink-0 text-sm font-extrabold tabular-nums">${{ number_format((float) $client->delivered_sales, 2) }}</span>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="py-8 text-center text-sm text-stone-500">Cuando completes una entrega, tus mejores
                            clientas aparecerán aquí.</p>
                    @endif
                </div>
            </details>
        </section>

        <div class="mb-4">
            <label for="search-orders" class="sr-only">Buscar pedido por cliente</label>
            <input id="search-orders" x-model.debounce.150ms="query" type="search"
                placeholder="Buscar por cliente o identificador"
                class="min-h-12 w-full rounded-xl border border-stone-300 bg-white px-4 text-base outline-none focus:border-[var(--brand)] focus:ring-2 focus:ring-rose-100">
        </div>

        @forelse ($orders as $order)
            @php
                $details = $order->items
                    ->map(fn($item) => '- ' . $item->product_name . ': $' . number_format((float) $item->price, 2))
                    ->implode("\n");
                $message =
                    "¡Hola {$order->client->name}! Tu pedido de SHEIN ya llegó 📦.\n\nDetalle de tus productos:\n{$details}\n\n*Total a pagar contra entrega: $" .
                    number_format((float) $order->total_amount, 2) .
                    "*\n(Nota: Los precios de los productos ya cuentan con la comisión por prenda incluida) ✨\n\nPuedes pasar por él a mi domicilio. ¡Avísame cuando vengas!\n\n— Enviado desde Tiendita Lupita 🛍️";
                $phone = preg_replace('/\D+/', '', $order->client->phone);
            @endphp
            <article data-live-search-item data-search="{{ $order->client->name }} {{ $order->client->internal_name }}"
                x-show="matches($el.dataset.search)" class="mb-4 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm"
                x-data="{ open: false, deleteOpen: false, partial: false, partialAmount: '' }">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-lg font-extrabold">{{ $order->client->name }}</p>
                        @if ($order->client->internal_name)
                            <p class="truncate text-xs font-semibold text-[var(--brand-dark)]">
                                {{ $order->client->internal_name }}</p>
                        @endif
                        <p class="mt-0.5 text-sm text-stone-500">Pedido #{{ $order->id }} ·
                            {{ $order->items->count() }} {{ $order->items->count() === 1 ? 'producto' : 'productos' }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800">Por
                        entregar</span>
                </div>
                <div class="my-4 space-y-1 border-y border-stone-100 py-3">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between gap-3 text-sm"><span
                                class="truncate text-stone-600">{{ $item->product_name }}</span><span
                                class="shrink-0 font-semibold">${{ number_format((float) $item->price, 2) }}</span></div>
                    @endforeach
                </div>
                <div class="mb-4 flex items-end justify-between"><span class="text-sm font-medium text-stone-500">Total a
                        cobrar</span><span
                        class="text-2xl font-extrabold tabular-nums">${{ number_format((float) $order->total_amount, 2) }}</span>
                </div>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <a href="https://wa.me/{{ $phone }}?text={{ rawurlencode($message) }}" target="_blank"
                        rel="noopener"
                        class="flex min-h-12 items-center justify-center gap-2 rounded-xl bg-emerald-700 px-3 text-sm font-bold text-white active:scale-[0.99]">
                        <svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true">
                            <path
                                d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0C5.6 0 .3 5.3.3 11.8c0 2.1.6 4.1 1.6 5.9L0 24l6.5-1.7a11.9 11.9 0 0 0 5.6 1.4h.1c6.5 0 11.8-5.3 11.8-11.8 0-3.2-1.2-6.2-3.5-8.4ZM12.1 21.7a9.9 9.9 0 0 1-5-1.4l-.4-.2-3.8 1 1-3.7-.2-.4a9.8 9.8 0 1 1 8.4 4.7Zm5.4-7.3c-.3-.1-1.6-.8-1.9-.9s-.5-.1-.7.2-.7.9-.9 1.1-.3.2-.6.1a7.9 7.9 0 0 1-2.3-1.4 8.6 8.6 0 0 1-1.6-2c-.2-.3 0-.4.1-.6l.5-.6c.1-.2.2-.3.3-.5s0-.4 0-.5-.7-1.7-1-2.3c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1 2.9 1.2 3.1c.1.2 2 3.1 4.8 4.3.7.3 1.2.5 1.7.6.7.2 1.3.2 1.8.1.6-.1 1.6-.7 1.8-1.3.2-.6.2-1.1.2-1.3s-.2-.3-.5-.5Z" />
                        </svg>
                        Notificar por WhatsApp
                    </a>
                    <button type="button" @click="open = true"
                        class="min-h-12 rounded-xl border border-stone-300 px-3 text-sm font-bold text-stone-800 active:bg-stone-50">Registrar
                        entrega</button>
                </div>

                <button type="button" @click="deleteOpen = true"
                    class="mt-2 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-rose-200 px-3 text-sm font-bold text-rose-700 transition hover:bg-rose-50">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 7h16m-10 4v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3" />
                    </svg>
                    Eliminar pedido
                </button>

                <div x-cloak x-show="deleteOpen" x-transition.opacity
                    class="fixed inset-0 z-50 flex items-end justify-center bg-stone-950/60 p-3 sm:items-center"
                    @keydown.escape.window="deleteOpen = false">
                    <div role="dialog" aria-modal="true" aria-labelledby="delete-order-title-{{ $order->id }}"
                        @click.outside="deleteOpen = false"
                        class="w-full max-w-sm rounded-2xl border border-stone-200 bg-white p-5 shadow-2xl">
                        <span class="grid size-11 place-items-center rounded-full bg-rose-50 text-rose-700">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v4m0 4h.01M10.3 3.9 1.8 18.6A1.6 1.6 0 0 0 3.2 21h17.6a1.6 1.6 0 0 0 1.4-2.4L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                            </svg>
                        </span>
                        <h2 id="delete-order-title-{{ $order->id }}" class="mt-4 text-lg font-extrabold">¿Eliminar
                            este pedido?</h2>
                        <p class="mt-2 text-sm leading-relaxed text-stone-600">Se eliminará el pedido de
                            {{ $order->client->name }} y todos sus productos. Esta acción no se puede deshacer.</p>
                        <form method="POST" action="{{ route('orders.destroy', $order) }}"
                            class="mt-5 grid grid-cols-2 gap-2">
                            @csrf
                            @method('DELETE')
                            <button type="button" @click="deleteOpen = false"
                                class="min-h-11 rounded-xl border border-stone-300 px-3 text-sm font-bold text-stone-700">Cancelar</button>
                            <button type="submit"
                                class="min-h-11 rounded-xl bg-rose-700 px-3 text-sm font-bold text-white">Sí,
                                eliminar</button>
                        </form>
                    </div>
                </div>

                <div x-cloak x-show="open" x-transition.opacity
                    class="fixed inset-0 z-50 flex items-end justify-center bg-stone-950/50 p-3 sm:items-center"
                    @keydown.escape.window="open = false">
                    <div @click.outside="open = false" class="w-full max-w-md rounded-3xl bg-white p-5 shadow-2xl">
                        <div class="mb-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-[var(--brand)]">Pedido
                                #{{ $order->id }}</p>
                            <h2 class="mt-1 text-xl font-extrabold">Registrar entrega</h2>
                            <p class="mt-1 text-sm text-stone-500">Se entrega el paquete completo, aunque quede saldo.</p>
                        </div>
                        <form method="POST" action="{{ route('orders.deliver', $order) }}" class="space-y-3">
                            @csrf
                            <button type="submit" name="payment_type" value="full" formnovalidate
                                @click="partial = false"
                                class="min-h-14 w-full rounded-xl bg-emerald-700 px-4 text-base font-extrabold text-white">Pagó
                                completo · ${{ number_format((float) $order->total_amount, 2) }}</button>
                            <div class="rounded-xl border border-stone-200 p-3">
                                <label for="paid-{{ $order->id }}" class="mb-2 block text-sm font-bold">Abono
                                    parcial</label>
                                <div class="relative"><span class="absolute left-3 top-3 text-stone-400">$</span><input
                                        id="paid-{{ $order->id }}" name="paid_amount" x-model.number="partialAmount"
                                        type="number" min="0" max="{{ $order->total_amount }}" step="0.01"
                                        placeholder="Cantidad que pagó"
                                        class="min-h-12 w-full rounded-xl border border-stone-300 pl-8 pr-3 text-base focus:border-[var(--brand)] focus:outline-none"
                                        :required="partial"></div>
                                <p class="mt-2 text-xs text-stone-500">Saldo pendiente: $<span
                                        x-text="Math.max(0, {{ (float) $order->total_amount }} - (Number(partialAmount) || 0)).toFixed(2)"></span>
                                </p>
                                <button type="submit" name="payment_type" value="partial" formtarget="_blank"
                                    @click="partial = true"
                                    class="mt-3 min-h-12 w-full rounded-xl bg-[var(--brand)] px-4 font-bold text-white">Registrar
                                    abono y entregar</button>
                            </div>
                            <button type="button" @click="open = false"
                                class="min-h-11 w-full text-sm font-semibold text-stone-500">Cancelar</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <section x-cloak x-show="!query.trim()"
                class="rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-12 text-center">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-rose-50 text-2xl">📦</span>
                <h2 class="mt-4 text-lg font-bold">Todo entregado por ahora</h2>
                <p class="mt-1 text-sm text-stone-500">Cuando llegue un encargo, crea un pedido y aparecerá aquí.</p>
                <a href="{{ route('orders.create') }}"
                    class="mt-5 inline-flex min-h-12 items-center rounded-xl bg-[var(--brand)] px-5 font-bold text-white">Crear
                    pedido</a>
            </section>
        @endforelse
        <section x-cloak x-show="query.trim() && !hasVisibleItems()"
            class="mb-4 rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-10 text-center">
            <h2 class="font-bold">No encontramos pedidos</h2>
            <p class="mt-1 text-sm text-stone-500">Prueba con otro nombre o identificador.</p>
        </section>
    </div>
@endsection
