<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-gray-900 flex items-center justify-center">
    <div class="text-center">
        <p class="text-9xl font-bold text-gray-700">403</p>
        <h1 class="text-2xl font-semibold text-gray-400 mt-4">Forbidden</h1>
        <p class="text-gray-500 mt-2">{{ $message ?? 'Access denied. Your request has been blocked.' }}</p>
    </div>
</body>
</html>