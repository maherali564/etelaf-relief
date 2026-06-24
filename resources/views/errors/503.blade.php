<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __("Under Maintenance") }}</title>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f8fafc; color: #1a202c; }
        .container { text-align: center; padding: 2rem; }
        h1 { font-size: 3rem; margin-bottom: 0.5rem; }
        p { font-size: 1.125rem; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ __("Under Maintenance") }}</h1>
        <p>{{ __("We are currently performing scheduled maintenance. We will be back shortly.") }}</p>
    </div>
</body>
</html>
