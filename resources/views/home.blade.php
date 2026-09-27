<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f7f3">
    <title>Tiendita Lupita | Hallazgos que se sienten tuyos</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[var(--canvas)] font-sans text-[var(--ink)] antialiased">
    <header class="absolute inset-x-0 top-0 z-10">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8" aria-label="Navegación principal">
            <a href="#inicio" class="flex items-center gap-3 text-white"><img src="{{ asset('favicon.svg') }}" alt="" class="size-10 shrink-0 rounded-full"><span class="text-sm font-extrabold">Tiendita Lupita</span></a>
            <div class="hidden items-center gap-7 text-sm font-semibold text-white/90 sm:flex"><a href="#colecciones" class="hover:text-white">Lo que encuentras</a><a href="#ventajas" class="hover:text-white">La experiencia</a><a href="#contacto" class="hover:text-white">Contacto</a></div>
            <a href="{{ route('login') }}" class="rounded-lg border border-white/60 px-4 py-2 text-xs font-bold text-white transition hover:bg-white hover:text-stone-900">Acceso admin</a>
        </nav>
    </header>

    <main>
        <section id="inicio" class="relative flex min-h-[650px] items-end overflow-hidden bg-stone-800 md:min-h-[720px]">
            <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=2200&q=85" alt="Amigas disfrutando un día de compras" class="absolute inset-0 size-full object-cover object-center" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-r from-stone-950/80 via-stone-900/40 to-stone-900/10"></div>
            <div class="relative mx-auto w-full max-w-7xl px-5 pb-16 pt-36 text-white sm:px-8 sm:pb-20 md:pb-24">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-200">Moda elegida con cariño</p>
                <h1 class="mt-4 max-w-3xl text-5xl font-black leading-[1.02] sm:text-6xl md:text-7xl">Tiendita Lupita</h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-white/85 sm:text-xl">Un lugar para encontrar eso que te encanta: novedades de SHEIN y favoritos para tu día a día.</p>
                <a href="#colecciones" class="mt-8 inline-flex min-h-12 items-center rounded-lg bg-[#df647b] px-6 text-sm font-bold text-white transition hover:bg-[#c64b66]">Explorar la tiendita <span class="ml-3" aria-hidden="true">↓</span></a>
            </div>
        </section>

        <section id="colecciones" class="mx-auto max-w-7xl px-5 py-16 sm:px-8 md:py-24">
            <div class="mb-9 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div><p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--brand)]">Para encontrar algo especial</p><h2 class="mt-2 text-3xl font-black sm:text-4xl">Piezas y novedades</h2></div>
                <p class="max-w-md text-sm leading-relaxed text-stone-600">Compra a tu manera, con atención cercana y nuevas opciones que van llegando a la tienda.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <article class="group relative min-h-80 overflow-hidden bg-stone-900 text-white">
                    <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=900&q=80" alt="Moda y tendencias" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-stone-950/85 via-stone-950/10 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-6"><p class="text-xs font-bold uppercase tracking-widest text-rose-200">Por encargo</p><h3 class="mt-2 text-2xl font-extrabold">SHEIN</h3><p class="mt-2 max-w-xs text-sm text-white/80">Arma tu pedido y te avisamos en cuanto llegue.</p></div>
                </article>
                <article class="group relative min-h-80 overflow-hidden bg-stone-700 text-white">
                    <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=900&q=80" alt="Interior de una tienda de ropa" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-stone-950/85 via-stone-950/10 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-6"><p class="text-xs font-bold uppercase tracking-widest text-rose-200">En persona</p><h3 class="mt-2 text-2xl font-extrabold">Tienda física</h3><p class="mt-2 max-w-xs text-sm text-white/80">Date una vuelta y descubre lo que tenemos disponible.</p></div>
                </article>
                <article class="group relative min-h-80 overflow-hidden bg-stone-700 text-white">
                    <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=900&q=80" alt="Selección de ropa y accesorios" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-stone-950/85 via-stone-950/10 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-6"><p class="text-xs font-bold uppercase tracking-widest text-rose-200">Recién llegado</p><h3 class="mt-2 text-2xl font-extrabold">Novedades</h3><p class="mt-2 max-w-xs text-sm text-white/80">Pequeños hallazgos y favoritos para renovar tu estilo.</p></div>
                </article>
            </div>
        </section>

        <section id="ventajas" class="border-y border-stone-200 bg-white">
            <div class="mx-auto grid max-w-7xl gap-8 px-5 py-14 sm:px-8 md:grid-cols-3 md:py-20">
                <div><span class="text-sm font-black text-[var(--brand)]">01</span><h3 class="mt-3 text-xl font-extrabold">Atención cercana</h3><p class="mt-2 text-sm leading-relaxed text-stone-600">Te acompañamos en cada compra y resolvemos tus dudas con claridad.</p></div>
                <div><span class="text-sm font-black text-[var(--brand)]">02</span><h3 class="mt-3 text-xl font-extrabold">Encargos sencillos</h3><p class="mt-2 text-sm leading-relaxed text-stone-600">Organizamos tu pedido SHEIN y mantenemos el seguimiento hasta la entrega.</p></div>
                <div><span class="text-sm font-black text-[var(--brand)]">03</span><h3 class="mt-3 text-xl font-extrabold">Nuevas opciones</h3><p class="mt-2 text-sm leading-relaxed text-stone-600">Visítanos para conocer prendas y novedades disponibles en tienda.</p></div>
            </div>
        </section>

        <section id="contacto" class="mx-auto grid max-w-7xl gap-10 px-5 py-16 sm:px-8 md:grid-cols-[1fr_0.8fr] md:py-24">
            <div><p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--brand)]">Nos encantará atenderte</p><h2 class="mt-2 text-3xl font-black sm:text-4xl">Pasa a saludar</h2><p class="mt-4 max-w-lg leading-relaxed text-stone-600">Escríbenos para consultar disponibilidad, novedades, horarios o cómo llegar.</p></div>
            <div class="border-l-2 border-[var(--brand)] pl-5">
                @if (config('app.contact_phone'))
                    <p class="text-sm font-bold">WhatsApp</p><a href="https://wa.me/{{ preg_replace('/\D+/', '', config('app.contact_phone')) }}" class="mt-1 block text-lg font-semibold text-[var(--brand-dark)]">{{ config('app.contact_phone') }}</a>
                @endif
                @if (config('app.business_location'))
                    <p class="mt-5 text-sm font-bold">Ubicación</p><p class="mt-1 text-stone-600">{{ config('app.business_location') }}</p>
                @endif
                @unless (config('app.contact_phone') || config('app.business_location'))
                    <p class="text-sm text-stone-600">Contáctanos para conocer los horarios y la ubicación de la tienda.</p>
                @endunless
            </div>
        </section>
    </main>

    <footer class="border-t border-stone-200 bg-[#eeeae4]">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-6 text-xs text-stone-600 sm:flex-row sm:items-center sm:justify-between sm:px-8"><span>© {{ date('Y') }} Tiendita Lupita</span><a href="{{ route('login') }}" class="font-semibold hover:text-stone-950">Acceso administrador</a></div>
    </footer>
</body>
</html>
