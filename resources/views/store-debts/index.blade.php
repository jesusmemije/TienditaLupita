@extends('layouts.app')

@section('title', 'Fiado de tienda')

@section('content')
    <div x-data="liveSearch()" data-initial-query="{{ $search }}" x-init="init($el, $el.dataset.initialQuery)">
        <div class="mb-5">
            <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand)]">Tienda física</p>
            <h1 class="mt-1 text-2xl font-extrabold">Fiado</h1>
            <p class="mt-1 text-sm text-stone-500">Cuentas independientes de los pedidos SHEIN</p>
            <p class="mt-1 text-sm text-stone-500"
                x-text="visibleCount() + ' ' + (visibleCount() === 1 ? 'cuenta' : 'cuentas')">{{ $debtors->count() }}
                {{ $debtors->count() === 1 ? 'cuenta' : 'cuentas' }}</p>
        </div>

        <details class="mb-5 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <summary class="min-h-8 cursor-pointer list-none font-bold">+ Agregar persona</summary>
            <form method="POST" action="{{ route('store-debts.store') }}" class="mt-4 space-y-3">
                @csrf
                <label for="debtor-name" class="sr-only">Nombre de la persona</label>
                <input id="debtor-name" name="name" type="text" maxlength="255" required value="{{ old('name') }}"
                    placeholder="Nombre de la persona"
                    class="min-h-12 w-full rounded-xl border border-stone-300 px-3 text-base focus:border-[var(--brand)] focus:outline-none">
                <button type="submit"
                    class="min-h-12 w-full rounded-xl bg-[var(--brand)] px-4 font-bold text-white">Guardar persona</button>
            </form>
        </details>

        <section class="mb-5 grid grid-cols-2 gap-3" aria-label="Estadísticas del fiado de tienda">
            <article class="border border-stone-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-semibold text-stone-500">Abonos recibidos</p>
                <p class="mt-1 text-2xl font-black tabular-nums text-emerald-700">${{ number_format($totalPaid, 2) }}</p>
            </article>
            <article class="border border-stone-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-semibold text-stone-500">Saldo pendiente</p>
                <p class="mt-1 text-2xl font-black tabular-nums text-[var(--brand-dark)]">
                    ${{ number_format($totalOwed, 2) }}</p>
            </article>
        </section>

        <div class="mb-4">
            <label for="debtor-search" class="sr-only">Buscar persona</label>
            <input id="debtor-search" name="search" value="{{ $search }}" x-model.debounce.150ms="query"
                type="search" placeholder="Buscar por nombre"
                class="min-h-12 w-full rounded-xl border border-stone-300 bg-white px-4 text-base outline-none focus:border-[var(--brand)] focus:ring-2 focus:ring-rose-100">
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($debtors as $debtor)
                <article data-live-search-item data-search="{{ $debtor->name }}" x-show="matches($el.dataset.search)"
                    class="min-w-0 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm" x-data="{ chargeOpen: false, paymentOpen: false, deleteOpen: false }">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-stone-400">Cuenta de tienda</p>
                            <h2 class="mt-1 break-words text-xl font-extrabold">{{ $debtor->name }}</h2>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-xs text-stone-500">Saldo pendiente</p>
                            <p
                                class="mt-1 text-xl font-black tabular-nums {{ $debtor->balance > 0 ? 'text-[var(--brand-dark)]' : 'text-emerald-700' }}">
                                ${{ number_format($debtor->balance, 2) }}</p>
                        </div>
                    </div>

                    <section class="mt-4 border-t border-stone-100 pt-3">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-stone-500">Movimientos</h3>
                        <ul class="mt-2 max-h-48 space-y-2 overflow-y-auto">
                            @forelse ($debtor->movements as $movement)
                                <li class="flex items-start justify-between gap-3 text-sm">
                                    <div class="min-w-0">
                                        <p class="break-words font-medium">
                                            {{ $movement->description ?: ($movement->type === 'charge' ? 'Cargo' : 'Abono') }}
                                        </p>
                                        <p class="text-xs text-stone-500">{{ $movement->movement_date->format('d/m/Y') }} ·
                                            {{ $movement->type === 'charge' ? 'Cargo' : 'Abono' }}</p>
                                    </div>
                                    <span
                                        @class([
                                            'shrink-0 font-bold tabular-nums',
                                            'text-rose-700' => $movement->type === 'charge',
                                            'text-emerald-700' => $movement->type === 'payment',
                                        ])>{{ $movement->type === 'charge' ? '+' : '−' }}${{ number_format((float) $movement->amount, 2) }}</span>
                                </li>
                            @empty
                                <li class="py-3 text-sm text-stone-500">Sin movimientos registrados.</li>
                            @endforelse
                        </ul>
                    </section>

                    <div class="mt-4 grid grid-cols-2 gap-2 border-t border-stone-100 pt-3">
                        <button type="button" @click="chargeOpen = !chargeOpen; paymentOpen = false"
                            class="min-h-11 rounded-lg border border-stone-300 px-2 text-sm font-bold">Nuevo cargo</button>
                        <button type="button" @click="paymentOpen = !paymentOpen; chargeOpen = false"
                            class="min-h-11 rounded-lg bg-emerald-700 px-2 text-sm font-bold text-white"
                            @disabled($debtor->balance <= 0)>Registrar abono</button>
                        <button type="button" @click="deleteOpen = true"
                            class="col-span-full inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-rose-200 px-2 text-sm font-bold text-rose-700 transition hover:bg-rose-50">
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 7h16m-10 4v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3" />
                            </svg>
                            Eliminar cuenta
                        </button>
                    </div>

                    <form x-cloak x-show="chargeOpen" method="POST"
                        action="{{ route('store-debts.charges.store', $debtor) }}"
                        class="mt-3 space-y-2 border-t border-stone-100 pt-3">
                        @csrf
                        <h3 class="text-sm font-bold">Agregar a la cuenta</h3>
                        <input name="description" type="text" maxlength="255" required placeholder="Prenda o descripción"
                            class="min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-[var(--brand)] focus:outline-none">
                        <input name="amount" type="number" min="0.01" step="0.01" required placeholder="Subtotal $"
                            class="min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm">
                        <button type="submit"
                            class="min-h-11 w-full rounded-lg bg-[var(--brand)] px-3 text-sm font-bold text-white">Guardar
                            cargo</button>
                    </form>

                    <form x-cloak x-show="paymentOpen" method="POST"
                        action="{{ route('store-debts.payments.store', $debtor) }}"
                        class="mt-3 space-y-2 border-t border-stone-100 pt-3">
                        @csrf
                        <h3 class="text-sm font-bold">Abono a la cuenta</h3>
                        <input name="description" type="text" maxlength="255" placeholder="Descripción (opcional)"
                            class="min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-[var(--brand)] focus:outline-none">
                        <input name="amount" type="number" min="0.01" max="{{ $debtor->balance }}"
                            step="0.01" required placeholder="Monto $"
                            class="min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm">
                        <button type="submit"
                            class="min-h-11 w-full rounded-lg bg-emerald-700 px-3 text-sm font-bold text-white">Guardar
                            abono</button>
                    </form>

                    <div x-cloak x-show="deleteOpen" x-transition.opacity
                        class="fixed inset-0 z-50 flex items-end justify-center bg-stone-950/60 p-3 sm:items-center"
                        @keydown.escape.window="deleteOpen = false">
                        <div role="dialog" aria-modal="true" aria-labelledby="delete-debtor-title-{{ $debtor->id }}"
                            @click.outside="deleteOpen = false"
                            class="w-full max-w-sm rounded-2xl border border-stone-200 bg-white p-5 shadow-2xl">
                            <span class="grid size-11 place-items-center rounded-full bg-rose-50 text-rose-700">
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor"
                                    stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v4m0 4h.01M10.3 3.9 1.8 18.6A1.6 1.6 0 0 0 3.2 21h17.6a1.6 1.6 0 0 0 1.4-2.4L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                                </svg>
                            </span>
                            <h2 id="delete-debtor-title-{{ $debtor->id }}" class="mt-4 text-lg font-extrabold">
                                ¿Eliminar esta cuenta?</h2>
                            <p class="mt-2 text-sm leading-relaxed text-stone-600">Se eliminará la cuenta de
                                {{ $debtor->name }} y todos sus cargos y abonos. Esta acción no se puede deshacer.</p>
                            <form method="POST" action="{{ route('store-debts.destroy', $debtor) }}"
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
                </article>
            @empty
                <section x-cloak x-show="!query.trim()"
                    class="rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-10 text-center lg:col-span-2">
                    <h2 class="font-bold">Todavía no hay cuentas</h2>
                    <p class="mt-1 text-sm text-stone-500">Agrega una persona para registrar su primer cargo.</p>
                </section>
            @endforelse
            <section x-cloak x-show="query.trim() && !hasVisibleItems()"
                class="rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-10 text-center lg:col-span-2">
                <h2 class="font-bold">No encontramos coincidencias</h2>
                <p class="mt-1 text-sm text-stone-500">Prueba con otro nombre.</p>
            </section>
        </div>
    </div>
@endsection
