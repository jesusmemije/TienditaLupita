@extends('layouts.app')

@section('title', 'Fiado de tienda')

@section('content')
<div class="mb-5 flex items-end justify-between gap-3">
    <div><p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand)]">Tienda física</p><h1 class="mt-1 text-2xl font-extrabold">Fiado</h1><p class="mt-1 text-sm text-stone-500">Cuentas independientes de los pedidos SHEIN</p></div>
</div>

<details class="mb-5 border-y border-stone-200 bg-white px-4 py-3">
    <summary class="min-h-9 cursor-pointer list-none font-bold">+ Agregar persona</summary>
    <form method="POST" action="{{ route('store-debts.store') }}" class="mt-3 flex flex-col gap-2 sm:flex-row">
        @csrf
        <label for="debtor-name" class="sr-only">Nombre de la persona</label>
        <input id="debtor-name" name="name" type="text" maxlength="255" required value="{{ old('name') }}" placeholder="Nombre de la persona" class="min-h-12 min-w-0 flex-1 rounded-lg border border-stone-300 px-3 text-base focus:border-[var(--brand)] focus:outline-none">
        <button type="submit" class="min-h-12 rounded-lg bg-[var(--brand)] px-5 text-sm font-bold text-white">Agregar</button>
    </form>
</details>

<div class="grid gap-4 lg:grid-cols-2">
    @forelse ($debtors as $debtor)
        <article class="min-w-0 border border-stone-200 bg-white p-4 shadow-sm" x-data="{ chargeOpen: false, paymentOpen: false }">
            <div class="flex items-start justify-between gap-3">
                <div><p class="text-xs font-bold uppercase tracking-wide text-stone-400">Cuenta de tienda</p><h2 class="mt-1 break-words text-xl font-extrabold">{{ $debtor->name }}</h2></div>
                <div class="shrink-0 text-right"><p class="text-xs text-stone-500">Saldo pendiente</p><p class="mt-1 text-xl font-black tabular-nums {{ $debtor->balance > 0 ? 'text-[var(--brand-dark)]' : 'text-emerald-700' }}">${{ number_format($debtor->balance, 2) }}</p></div>
            </div>

            <section class="mt-4 border-t border-stone-100 pt-3">
                <h3 class="text-xs font-bold uppercase tracking-wide text-stone-500">Movimientos</h3>
                <ul class="mt-2 max-h-48 space-y-2 overflow-y-auto">
                    @forelse ($debtor->movements as $movement)
                        <li class="flex items-start justify-between gap-3 text-sm">
                            <div class="min-w-0"><p class="break-words font-medium">{{ $movement->description ?: ($movement->type === 'charge' ? 'Cargo' : 'Abono') }}</p><p class="text-xs text-stone-500">{{ $movement->movement_date->format('d/m/Y') }} · {{ $movement->type === 'charge' ? 'Cargo' : 'Abono' }}</p></div>
                            <span @class(['shrink-0 font-bold tabular-nums', 'text-rose-700' => $movement->type === 'charge', 'text-emerald-700' => $movement->type === 'payment'])>{{ $movement->type === 'charge' ? '+' : '−' }}${{ number_format((float) $movement->amount, 2) }}</span>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-stone-500">Sin movimientos registrados.</li>
                    @endforelse
                </ul>
            </section>

            <div class="mt-4 grid grid-cols-2 gap-2 border-t border-stone-100 pt-3">
                <button type="button" @click="chargeOpen = !chargeOpen; paymentOpen = false" class="min-h-11 rounded-lg border border-stone-300 px-2 text-sm font-bold">Nuevo cargo</button>
                <button type="button" @click="paymentOpen = !paymentOpen; chargeOpen = false" class="min-h-11 rounded-lg bg-emerald-700 px-2 text-sm font-bold text-white" @disabled($debtor->balance <= 0)>Registrar abono</button>
            </div>

            <form x-cloak x-show="chargeOpen" method="POST" action="{{ route('store-debts.charges.store', $debtor) }}" class="mt-3 space-y-2 border-t border-stone-100 pt-3">
                @csrf
                <h3 class="text-sm font-bold">Agregar a la cuenta</h3>
                <input name="description" type="text" maxlength="255" required placeholder="Prenda o descripción" class="min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-[var(--brand)] focus:outline-none">
                <div class="grid grid-cols-2 gap-2"><input name="amount" type="number" min="0.01" step="0.01" required placeholder="Subtotal $" class="min-h-11 min-w-0 rounded-lg border border-stone-300 px-3 text-sm"><input name="movement_date" type="date" value="{{ now()->toDateString() }}" required class="min-h-11 min-w-0 rounded-lg border border-stone-300 px-2 text-sm"></div>
                <button type="submit" class="min-h-11 w-full rounded-lg bg-[var(--brand)] px-3 text-sm font-bold text-white">Guardar cargo</button>
            </form>

            <form x-cloak x-show="paymentOpen" method="POST" action="{{ route('store-debts.payments.store', $debtor) }}" class="mt-3 space-y-2 border-t border-stone-100 pt-3">
                @csrf
                <h3 class="text-sm font-bold">Abono a la cuenta</h3>
                <input name="description" type="text" maxlength="255" placeholder="Descripción (opcional)" class="min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-[var(--brand)] focus:outline-none">
                <div class="grid grid-cols-2 gap-2"><input name="amount" type="number" min="0.01" max="{{ $debtor->balance }}" step="0.01" required placeholder="Monto $" class="min-h-11 min-w-0 rounded-lg border border-stone-300 px-3 text-sm"><input name="movement_date" type="date" value="{{ now()->toDateString() }}" required class="min-h-11 min-w-0 rounded-lg border border-stone-300 px-2 text-sm"></div>
                <button type="submit" class="min-h-11 w-full rounded-lg bg-emerald-700 px-3 text-sm font-bold text-white">Guardar abono</button>
            </form>
        </article>
    @empty
        <section class="border border-dashed border-stone-300 bg-white px-5 py-12 text-center lg:col-span-2"><h2 class="text-lg font-bold">Todavía no hay cuentas</h2><p class="mt-1 text-sm text-stone-500">Agrega una persona para registrar su primer cargo.</p></section>
    @endforelse
</div>
@endsection
