<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10131c">
    <title>@yield('title', '10bob WiFi')</title>
    {{ Vite::fonts('instrument-sans') }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-page">
    <main class="portal-frame">
        @yield('content')
        <footer class="portal-footer">
            <span>10bob WiFi</span>
            <a href="{{ route('terms') }}">Terms &amp; conditions</a>
        </footer>
    </main>
</body>
</html>