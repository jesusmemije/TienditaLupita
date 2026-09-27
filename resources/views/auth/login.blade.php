<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Acceso administrador | Tiendita Lupita</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[var(--canvas)] font-sans text-[var(--ink)] antialiased">
    <main class="mx-auto grid min-h-screen max-w-5xl items-center gap-10 px-5 py-10 md:grid-cols-[1fr_380px] md:px-10">
        <section class="hidden md:block">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 text-sm font-bold text-stone-600"><img src="{{ asset('favicon.svg') }}" alt="" class="size-10 shrink-0 rounded-full"> Tiendita Lupita</a>
            <p class="mt-14 text-xs font-bold uppercase tracking-[0.15em] text-[var(--brand)]">Administración</p>
            <h1 class="mt-3 max-w-lg text-5xl font-black leading-[1.05]">Tu negocio, al día.</h1>
            <p class="mt-4 max-w-md text-lg text-stone-600">Pedidos SHEIN y cuentas de tienda en un solo panel privado.</p>
        </section>

        <section class="mx-auto w-full max-w-sm">
            <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-3 text-sm font-bold md:hidden"><img src="{{ asset('favicon.svg') }}" alt="" class="size-10 shrink-0 rounded-full"> Tiendita Lupita</a>
            <div class="border-y border-stone-300 py-8 md:border md:border-stone-200 md:bg-white md:px-7 md:shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--brand)]">Acceso privado</p>
                <h2 class="mt-2 text-2xl font-extrabold">Iniciar sesión</h2>
                <p class="mt-1 text-sm text-stone-500">Ingresa con la cuenta administradora.</p>
                <form method="POST" action="{{ route('login.store') }}" class="mt-7 space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-bold">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="min-h-12 w-full rounded-lg border border-stone-300 px-3 outline-none focus:border-[var(--brand)] focus:ring-2 focus:ring-rose-100">
                        @error('email')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-bold">Contraseña</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="min-h-12 w-full rounded-lg border border-stone-300 px-3 outline-none focus:border-[var(--brand)] focus:ring-2 focus:ring-rose-100">
                        @error('password')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-2 text-sm text-stone-600"><input name="remember" type="checkbox" value="1" class="size-4 rounded border-stone-300 accent-[var(--brand)]"> Mantener sesión iniciada</label>
                    <button type="submit" class="min-h-12 w-full rounded-lg bg-[var(--brand)] px-4 font-bold text-white">Entrar al panel</button>
                </form>
            </div>
            <a href="{{ route('home') }}" class="mt-5 inline-block text-sm font-semibold text-stone-500 hover:text-stone-900">Volver a la tienda</a>
        </section>
    </main>
</body>
</html>
