<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config("app.name") }}</title>
    @vite(["resources/css/app.css", "resources/js/app.js"])
</head>
<body class="h-full bg-[#F3F4F6] text-[#333333] font-sans antialiased flex items-center justify-center">
    {{ $slot }}
</body>
</html>
