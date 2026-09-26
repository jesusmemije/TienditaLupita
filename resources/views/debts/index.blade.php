@extends('layouts.app')

@section('title', 'Cuentas pendientes')

@section('content')
<div class="mb-5">
    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand)]">Seguimiento</p>
    <h1 class="mt-1 text-2xl font-extrabold">Cuentas pendientes</h1>
    <p class="mt-1 text-sm text-stone-500">{{ $orders->count() }} {{ $orders->count() === 1 ? 'saldo por cobrar' : 'saldos por cobrar' }}</p>
</div>

@forelse ($orders as $order)
    @php
        $message = "Hola {$order->client->name}, recordatorio amistoso de tu saldo pendiente de $".number_format((float) $order->due_amount, 2)." de tu pedido anterior en Tiendita Lupita 🛍️.\n\n— Enviado desde Tiendita Lupita 🛍️";
        $phone = preg_replace('/\D+/', '', $order->client->phone);
    @endphp
    <article class="mb-4 rounded-2xl border border-rose-100 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div><h2 class="text-lg font-extrabold">{{ $order->client->name }}</h2><p class="mt-0.5 text-sm text-stone-500">Pedido #{{ $order->id }} · {{ optional($order->delivered_at)->format('d/m/Y') }}</p></div>
            <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-800">Saldo pendiente</span>
        </div>
        <div class="my-4 flex items-end justify-between border-y border-stone-100 py-3"><span class="text-sm text-stone-500">Debe</span><strong class="text-2xl font-extrabold tabular-nums text-[var(--brand-dark)]">${{ number_format((float) $order->due_amount, 2) }}</strong></div>
        <div class="grid gap-2 sm:grid-cols-2">
            <a href="https://wa.me/{{ $phone }}?text={{ rawurlencode($message) }}" target="_blank" rel="noopener" class="flex min-h-12 items-center justify-center gap-2 rounded-xl bg-emerald-700 px-3 text-sm font-bold text-white">Recordatorio WhatsApp</a>
            <form method="POST" action="{{ route('debts.settle', $order) }}" onsubmit="return confirm('¿Confirmas que se liquidó el saldo de ${{ number_format((float) $order->due_amount, 2) }}?')">
                @csrf
                <button type="submit" class="min-h-12 w-full rounded-xl border border-stone-300 px-3 text-sm font-bold text-stone-800">Liquidar saldo</button>
            </form>
        </div>
    </article>
@empty
    <section class="rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-12 text-center"><span class="mx-auto grid size-14 place-items-center rounded-2xl bg-emerald-50 text-2xl">✓</span><h2 class="mt-4 text-lg font-bold">Sin saldos pendientes</h2><p class="mt-1 text-sm text-stone-500">Los abonos que registres aparecerán aquí.</p></section>
@endforelse
@endsection
