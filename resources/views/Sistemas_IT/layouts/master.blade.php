<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sistema de Tickets - E&I Tecnología')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/Sistemas_IT/inventario-index.css'])
    @stack('styles')
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-100">
    @include('layouts.navigation')

    @yield('header')

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>