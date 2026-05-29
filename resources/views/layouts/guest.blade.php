<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <title>{{ config('app.name', 'Grand Creek Hotel & Resort') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <link rel="stylesheet" href="/css/app.css">

        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-emerald-950">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">
            <!-- Branding -->
            <div class="text-center mb-10">
                <a href="/" class="inline-flex flex-col items-center gap-3">
                    <x-application-logo class="w-24 h-24" />
                    <div>
                        <h1 class="text-3xl font-bold text-white tracking-wider">Grand Creek</h1>
                        <p class="text-sm text-emerald-300/80 -mt-1">Hotel &amp; Resort</p>
                    </div>
                </a>
            </div>

            <!-- Auth Card -->
            <div class="w-full sm:max-w-md bg-white rounded-2xl shadow-2xl shadow-black/20 px-9 py-9">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <p class="mt-8 text-xs text-white/40">&copy; {{ date('Y') }} Grand Creek Hotel &amp; Resort. All rights reserved.</p>
        </div>
    </body>
</html>
