<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Gestión Documental') }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📄</text></svg>">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />

    <meta name="description" content="Sistema de gestión documental con manejo de Excel y PDFs">
    <meta name="CDFT" content="Gestión Documental">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased">
    <div id="app"></div>

    <noscript>
        <div style="text-align: center; padding: 50px; font-family: sans-serif;">
            <h2>JavaScript Required</h2>
            <p>Esta aplicación requiere JavaScript para funcionar correctamente.</p>
            <p>Por favor, habilita JavaScript en tu navegador.</p>
        </div>
    </noscript>
</body>

</html>