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
            <a href="https://wa.me/{{ $phone }}?text={{ rawurlencode($message) }}" target="_blank" rel="noopener" class="flex min-h-12 items-center justify-center gap-2 rounded-lg bg-emerald-700 px-3 text-sm font-bold text-white">
                <svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true"><path d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0C5.6 0 .3 5.3.3 11.8c0 2.1.6 4.1 1.6 5.9L0 24l6.5-1.7a11.9 11.9 0 0 0 5.6 1.4h.1c6.5 0 11.8-5.3 11.8-11.8 0-3.2-1.2-6.2-3.5-8.4ZM12.1 21.7a9.9 9.9 0 0 1-5-1.4l-.4-.2-3.8 1 1-3.7-.2-.4a9.8 9.8 0 1 1 8.4 4.7Zm5.4-7.3c-.3-.1-1.6-.8-1.9-.9s-.5-.1-.7.2-.7.9-.9 1.1-.3.2-.6.1a7.9 7.9 0 0 1-2.3-1.4 8.6 8.6 0 0 1-1.6-2c-.2-.3 0-.4.1-.6l.5-.6c.1-.2.2-.3.3-.5s0-.4 0-.5-.7-1.7-1-2.3c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1 2.9 1.2 3.1c.1.2 2 3.1 4.8 4.3.7.3 1.2.5 1.7.6.7.2 1.3.2 1.8.1.6-.1 1.6-.7 1.8-1.3.2-.6.2-1.1.2-1.3s-.2-.3-.5-.5Z"/></svg>
                Recordatorio WhatsApp
            </a>
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
