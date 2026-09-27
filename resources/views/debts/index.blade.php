@extends('layouts.app')

@section('title', 'Cuentas pendientes')

@section('content')
<div class="mb-5">
    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand)]">Pedidos SHEIN</p>
    <h1 class="mt-1 text-2xl font-extrabold">Cuentas pendientes</h1>
    <p class="mt-1 text-sm text-stone-500">{{ $orders->count() }} {{ $orders->count() === 1 ? 'saldo por cobrar' : 'saldos por cobrar' }}</p>
</div>

@forelse ($orders as $order)
    @php
        $details = $order->items
            ->map(fn ($item) => '- '.$item->product_name.': $'.number_format((float) $item->price, 2))
            ->implode("\n");
        $message = "¡Hola {$order->client->name}! 😊 Te envío un cordial recordatorio con el estado de tu pedido de SHEIN:\n\n"
            ."📦 *Detalle de tus productos:*\n{$details}\n\n"
            .'💰 *Total del pedido:* $'.number_format((float) $order->total_amount, 2)."\n"
            .'✅ *Abono/Anticipo registrado:* $'.number_format((float) $order->paid_amount, 2)."\n"
            .'📌 *Resta por liquidar:* $'.number_format((float) $order->due_amount, 2)."\n\n"
            .'Quedo a tus órdenes si tienes alguna duda o cuando gustes realizar tu pago. ¡Que tengas un excelente día! ✨' ."\n\n"
            .'— Tiendita Lupita 🛍️';
        $phone = preg_replace('/\D+/', '', $order->client->phone);
    @endphp
    <article class="mb-4 rounded-xl border border-rose-100 bg-white p-4 shadow-sm" x-data="{ amount: '', error: '' }">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0"><h2 class="truncate text-lg font-extrabold">{{ $order->client->name }}</h2>@if ($order->client->internal_name)<p class="truncate text-xs font-semibold text-[var(--brand-dark)]">{{ $order->client->internal_name }}</p>@endif<p class="mt-0.5 text-sm text-stone-500">Pedido #{{ $order->id }} · {{ optional($order->delivered_at)->format('d/m/Y') }}</p></div>
            <span class="shrink-0 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-800">Saldo pendiente</span>
        </div>
        <div class="my-4 flex items-end justify-between border-y border-stone-100 py-3"><span class="text-sm text-stone-500">Debe</span><strong class="text-2xl font-extrabold tabular-nums text-[var(--brand-dark)]">${{ number_format((float) $order->due_amount, 2) }}</strong></div>
        <div class="grid gap-2 sm:grid-cols-2">
            <a href="https://wa.me/{{ $phone }}?text={{ rawurlencode($message) }}" target="_blank" rel="noopener" class="flex min-h-12 items-center justify-center gap-2 rounded-lg bg-emerald-700 px-3 text-sm font-bold text-white">Recordatorio WhatsApp</a>
            <button type="submit" form="settle-order-{{ $order->id }}" class="min-h-12 w-full rounded-lg border border-stone-300 px-3 text-sm font-bold text-stone-800">Liquidar deuda</button>
        </div>
        <form method="POST" action="{{ route('debts.payments.store', $order) }}" class="mt-3 rounded-lg bg-stone-50 p-3" @submit="if (!Number(amount) || Number(amount) <= 0) { error = 'Por favor ingresa el monto a abonar'; $event.preventDefault() }">
            @csrf
            <label for="payment-{{ $order->id }}" class="mb-1.5 block text-sm font-bold">Registrar abono</label>
            <div class="flex gap-2">
                <div class="relative min-w-0 flex-1"><span class="absolute left-3 top-3 text-stone-400">$</span><input id="payment-{{ $order->id }}" name="paid_amount" x-model="amount" type="number" min="0.01" max="{{ $order->due_amount }}" step="0.01" placeholder="Monto" class="min-h-12 w-full rounded-lg border border-stone-300 bg-white pl-8 pr-3 text-base focus:border-[var(--brand)] focus:outline-none"></div>
                <button type="submit" class="min-h-12 rounded-lg bg-[var(--brand)] px-5 text-sm font-bold text-white">Abonar</button>
            </div>
            <p x-show="error" x-text="error" class="mt-2 text-sm text-rose-700" role="alert"></p>
        </form>
    </article>
    <form id="settle-order-{{ $order->id }}" method="POST" action="{{ route('debts.settle', $order) }}" hidden>
        @csrf
    </form>
@empty
    <section class="rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-12 text-center"><span class="mx-auto grid size-14 place-items-center rounded-2xl bg-emerald-50 text-2xl">✓</span><h2 class="mt-4 text-lg font-bold">Sin saldos pendientes</h2><p class="mt-1 text-sm text-stone-500">Los abonos que registres aparecerán aquí.</p></section>
@endforelse
@endsection
