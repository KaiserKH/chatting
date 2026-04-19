<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased theme-light" x-data="{ theme: localStorage.getItem('theme') || 'theme-light' }" x-init="document.body.classList.remove('theme-light','theme-dark','theme-romantic'); document.body.classList.add(theme)">
        <div class="min-h-screen bg-transparent">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-3 flex justify-end">
                <label class="text-xs text-gray-600 flex items-center gap-2 bg-white/80 rounded-full px-3 py-1 shadow-sm">
                    Theme
                    <select x-model="theme" class="text-xs border-gray-300 rounded-md" @change="localStorage.setItem('theme', theme); document.body.classList.remove('theme-light','theme-dark','theme-romantic'); document.body.classList.add(theme)">
                        <option value="theme-light">Light</option>
                        <option value="theme-dark">Dark</option>
                        <option value="theme-romantic">Romantic</option>
                    </select>
                </label>
            </div>
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
