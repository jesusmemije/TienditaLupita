@extends('layouts.app')

@section('title', 'Nuevo pedido')

@section('content')
<section x-data="orderForm(@js($clients->map(fn ($client) => ['id' => $client->id, 'name' => $client->name])->values()))">
    <div class="mb-5">
        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--brand)]">Nuevo encargo</p>
        <h1 class="mt-1 text-2xl font-extrabold">Crear pedido</h1>
        <p class="mt-1 text-sm text-stone-500">Agrega los productos y el total se calcula al instante.</p>
    </div>

    <form method="POST" action="{{ route('orders.store') }}" class="space-y-5">
        @csrf
        <section class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between gap-3">
                <label for="client_id" class="text-sm font-bold">Cliente</label>
                <button type="button" @click="showClientModal = true; clientError = ''" class="min-h-10 rounded-xl bg-rose-50 px-3 text-sm font-bold text-[var(--brand-dark)]">+ Nuevo cliente</button>
            </div>
            <select id="client_id" name="client_id" x-model="selectedClient" required class="min-h-12 w-full rounded-xl border border-stone-300 bg-white px-3 text-base outline-none focus:border-[var(--brand)] focus:ring-2 focus:ring-rose-100">
                <option value="">Elige un cliente</option>
                <template x-for="client in clients" :key="client.id"><option :value="client.id" x-text="client.name"></option></template>
            </select>
            @error('client_id')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
        </section>

        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold">Productos</h2>
                <button type="button" @click="addItem()" class="inline-flex min-h-10 items-center gap-1 rounded-xl px-3 text-sm font-bold text-[var(--brand-dark)] hover:bg-rose-50">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg> Agregar
                </button>
            </div>
            <template x-for="(item, index) in items" :key="item.key">
                <article class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide text-stone-400" x-text="`Producto ${index + 1}`"></span>
                        <button type="button" x-show="items.length > 1" @click="items.splice(index, 1)" class="min-h-9 px-2 text-sm font-semibold text-rose-700">Quitar</button>
                    </div>
                    <label class="mb-1 block text-xs font-semibold text-stone-500" :for="`product-${index}`">Nombre del producto</label>
                    <input :id="`product-${index}`" :name="`items[${index}][product_name]`" x-model="item.product_name" type="text" maxlength="255" required placeholder="Ej. Blusa floral" class="min-h-12 w-full rounded-xl border border-stone-300 px-3 text-base outline-none focus:border-[var(--brand)] focus:ring-2 focus:ring-rose-100">
                    <label class="mb-1 mt-3 block text-xs font-semibold text-stone-500" :for="`price-${index}`">Precio (MXN)</label>
                    <div class="relative"><span class="absolute left-3 top-3.5 text-stone-400">$</span><input :id="`price-${index}`" :name="`items[${index}][price]`" x-model.number="item.price" type="number" min="0.01" max="99999999.99" step="0.01" required placeholder="0.00" class="min-h-12 w-full rounded-xl border border-stone-300 pl-8 pr-3 text-base outline-none focus:border-[var(--brand)] focus:ring-2 focus:ring-rose-100"></div>
                </article>
            </template>
            @if ($errors->has('items') || $errors->has('items.*.product_name') || $errors->has('items.*.price'))
                <p class="text-sm text-rose-700">{{ $errors->first('items') ?: $errors->first('items.*.product_name') ?: $errors->first('items.*.price') }}</p>
            @endif
        </section>

        <section class="rounded-2xl bg-stone-900 p-5 text-white">
            <p class="text-sm font-medium text-stone-300">Total contra entrega</p>
            <p class="mt-1 text-3xl font-extrabold tabular-nums" x-text="formatMoney(total)"></p>
        </section>

        <button type="submit" class="min-h-14 w-full rounded-2xl bg-[var(--brand)] px-5 text-base font-extrabold text-white shadow-lg shadow-rose-900/15 active:scale-[0.99]">Crear pedido</button>
    </form>

    <div x-cloak x-show="showClientModal" x-transition.opacity class="fixed inset-0 z-50 flex items-end justify-center bg-stone-950/50 p-3 sm:items-center" @keydown.escape.window="showClientModal = false">
        <div @click.outside="showClientModal = false" class="w-full max-w-md rounded-3xl bg-white p-5 shadow-2xl">
            <div class="mb-4 flex items-start justify-between">
                <div><p class="text-xs font-bold uppercase tracking-wide text-[var(--brand)]">Agenda</p><h2 class="mt-1 text-xl font-extrabold">Nuevo cliente</h2></div>
                <button type="button" @click="showClientModal = false" aria-label="Cerrar" class="grid size-10 place-items-center rounded-full bg-stone-100 text-xl">×</button>
            </div>
            <form @submit.prevent="createClient()" class="space-y-3">
                <input x-model="newClient.name" type="text" required maxlength="255" placeholder="Nombre completo" class="min-h-12 w-full rounded-xl border border-stone-300 px-3 text-base focus:border-[var(--brand)] focus:outline-none">
                <input x-model="newClient.phone" type="tel" required maxlength="30" placeholder="WhatsApp con clave de país" class="min-h-12 w-full rounded-xl border border-stone-300 px-3 text-base focus:border-[var(--brand)] focus:outline-none">
                <p x-show="clientError" x-text="clientError" class="text-sm text-rose-700" role="alert"></p>
                <button type="submit" :disabled="savingClient" class="min-h-13 w-full rounded-xl bg-[var(--brand)] px-4 font-bold text-white disabled:opacity-60" x-text="savingClient ? 'Guardando…' : 'Guardar y seleccionar'"></button>
            </form>
        </div>
    </div>
</section>
@endsection
