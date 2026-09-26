@extends('layouts.app')

@section('title', 'Historial')

@section('content')
<div class="mb-5"><p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand)]">Registro</p><h1 class="mt-1 text-2xl font-extrabold">Pedidos entregados</h1><p class="mt-1 text-sm text-stone-500">{{ $orders->count() }} {{ $orders->count() === 1 ? 'pedido' : 'pedidos' }} en el historial</p></div>

@forelse ($orders as $order)
    <article class="mb-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3"><div><h2 class="text-lg font-extrabold">{{ $order->client->name }}</h2>@if ($order->client->internal_name)<p class="text-xs font-semibold text-[var(--brand-dark)]">{{ $order->client->internal_name }}</p>@endif<p class="mt-0.5 text-sm text-stone-500">Pedido #{{ $order->id }} · {{ optional($order->delivered_at)->format('d/m/Y') }}</p></div><span @class(['rounded-full px-3 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-800' => $order->status === 'delivered_paid', 'bg-amber-50 text-amber-800' => $order->status === 'delivered_partial'])>{{ $order->status === 'delivered_paid' ? 'Pagado' : 'Abono parcial' }}</span></div>
        <div class="mt-4 grid grid-cols-3 gap-2 border-t border-stone-100 pt-3 text-sm"><div><p class="text-xs text-stone-500">Total</p><p class="mt-1 font-bold">${{ number_format((float) $order->total_amount, 2) }}</p></div><div><p class="text-xs text-stone-500">Pagado</p><p class="mt-1 font-bold">${{ number_format((float) $order->paid_amount, 2) }}</p></div><div><p class="text-xs text-stone-500">Pendiente</p><p class="mt-1 font-bold">${{ number_format((float) $order->due_amount, 2) }}</p></div></div>
    </article>
@empty
    <section class="rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-12 text-center"><h2 class="text-lg font-bold">Todavía no hay entregas</h2><p class="mt-1 text-sm text-stone-500">Al registrar una entrega, aparecerá aquí.</p></section>
@endforelse
@endsection
