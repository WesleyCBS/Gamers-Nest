<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Game Nest') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased" style="background-color: #0d1934;">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10" style="background-color: #0d1934;">
            <div class="mb-6">
                                <a href="/" class="block text-center" style="font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 2.5rem; letter-spacing: 0.15em; color: #38bdf8; text-shadow: 0 0 12px rgba(56,189,248,0.7), 0 0 30px rgba(56,189,248,0.35);">
                    GAMERS NEST
                </a>
            </div>

            <div class="w-full sm:max-w-md bg-white px-8 py-8 shadow-2xl rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>