@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<div x-data="liveSearch()" data-initial-query="{{ $search }}" x-init="init($el, $el.dataset.initialQuery)">
<div class="mb-5">
    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand)]">Tu comunidad</p>
    <h1 class="mt-1 text-2xl font-extrabold">Clientes</h1>
    <p class="mt-1 text-sm text-stone-500" x-text="visibleCount() + ' ' + (visibleCount() === 1 ? 'cliente' : 'clientes')">{{ $clients->count() }} {{ $clients->count() === 1 ? 'cliente' : 'clientes' }}</p>
</div>

    <div class="mb-4">
        <label for="search-client" class="sr-only">Buscar cliente</label>
        <input id="search-client" name="q" value="{{ $search }}" x-model.debounce.150ms="query" type="search" placeholder="Buscar por nombre, identificador o teléfono" class="min-h-12 w-full rounded-xl border border-stone-300 bg-white px-4 text-base outline-none focus:border-[var(--brand)] focus:ring-2 focus:ring-rose-100">
    </div>

<details class="mb-5 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
    <summary class="min-h-8 cursor-pointer list-none font-bold">+ Agregar cliente</summary>
    <form method="POST" action="{{ route('clients.store') }}" class="mt-4 space-y-3">
        @csrf
        <input name="name" type="text" maxlength="255" required value="{{ old('name') }}" placeholder="Nombre formal (para WhatsApp)" class="min-h-12 w-full rounded-xl border border-stone-300 px-3 text-base focus:border-[var(--brand)] focus:outline-none">
        <input name="internal_name" type="text" maxlength="255" value="{{ old('internal_name') }}" placeholder="Identificador interno (opcional)" class="min-h-12 w-full rounded-xl border border-stone-300 px-3 text-base focus:border-[var(--brand)] focus:outline-none">
        <input name="phone" type="tel" maxlength="30" required value="{{ old('phone') }}" placeholder="WhatsApp con clave de país" class="min-h-12 w-full rounded-xl border border-stone-300 px-3 text-base focus:border-[var(--brand)] focus:outline-none">
        <textarea name="notes" rows="2" placeholder="Notas (opcional)" class="w-full rounded-xl border border-stone-300 px-3 py-3 text-base focus:border-[var(--brand)] focus:outline-none">{{ old('notes') }}</textarea>
        <button type="submit" class="min-h-12 w-full rounded-xl bg-[var(--brand)] px-4 font-bold text-white">Guardar cliente</button>
    </form>
</details>

<div class="space-y-3">
    @forelse ($clients as $client)
        <article data-live-search-item data-search="{{ $client->name }} {{ $client->internal_name }} {{ $client->phone }}" x-show="matches($el.dataset.search)" class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3"><div class="min-w-0"><h2 class="truncate text-lg font-extrabold">{{ $client->name }}</h2>@if ($client->internal_name)<p class="mt-0.5 text-xs font-semibold text-[var(--brand-dark)]">Identificador interno: {{ $client->internal_name }}</p>@endif<a href="tel:{{ $client->phone }}" class="mt-1 block text-sm text-stone-500">{{ $client->phone }}</a></div><span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-rose-50 font-bold text-[var(--brand-dark)]">{{ mb_strtoupper(mb_substr($client->name, 0, 1)) }}</span></div>
            @if ($client->notes)<p class="mt-3 text-sm text-stone-600">{{ $client->notes }}</p>@endif
            <div class="mt-4 grid grid-cols-2 gap-3 border-t border-stone-100 pt-3"><div><p class="text-xs font-semibold text-stone-500">Total pagado</p><p class="mt-1 font-extrabold tabular-nums">${{ number_format((float) ($client->total_paid ?? 0), 2) }}</p></div><div><p class="text-xs font-semibold text-stone-500">Saldo pendiente</p><p class="mt-1 font-extrabold tabular-nums {{ (float) ($client->current_due ?? 0) > 0 ? 'text-[var(--brand-dark)]' : '' }}">${{ number_format((float) ($client->current_due ?? 0), 2) }}</p></div></div>
            <details class="mt-3 border-t border-stone-100 pt-3">
                <summary class="min-h-9 cursor-pointer text-sm font-bold text-stone-700">Editar datos del cliente</summary>
                <form method="POST" action="{{ route('clients.update', $client) }}" class="mt-3 space-y-2">
                    @csrf
                    @method('PUT')
                    <input name="name" type="text" maxlength="255" required value="{{ $client->name }}" aria-label="Nombre formal" class="min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm">
                    <input name="internal_name" type="text" maxlength="255" value="{{ $client->internal_name }}" placeholder="Identificador interno (opcional)" aria-label="Identificador interno" class="min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm">
                    <input name="phone" type="tel" maxlength="30" required value="{{ $client->phone }}" aria-label="WhatsApp" class="min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm">
                    <textarea name="notes" rows="2" aria-label="Notas" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">{{ $client->notes }}</textarea>
                    <button type="submit" class="min-h-10 rounded-lg bg-stone-900 px-4 text-sm font-bold text-white">Guardar cambios</button>
                </form>
            </details>
        </article>
    @empty
        <section x-cloak x-show="!query.trim()" class="rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-10 text-center"><h2 class="font-bold">Aún no hay clientes</h2><p class="mt-1 text-sm text-stone-500">Agrega un cliente para comenzar su pedido.</p></section>
    @endforelse
    <section x-cloak x-show="query.trim() && !hasVisibleItems()" class="rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-10 text-center"><h2 class="font-bold">No encontramos coincidencias</h2><p class="mt-1 text-sm text-stone-500">Prueba con otro nombre, identificador o teléfono.</p></section>
</div>
</div>
@endsection
