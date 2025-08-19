<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Laravel Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; padding: 20px; background: #f0f8ff; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success">
            <h1>✅ Laravel is Working!</h1>
            <p><strong>App Name:</strong> {{ config('app.name') }}</p>
            <p><strong>Environment:</strong> {{ config('app.env') }}</p>
            <p><strong>Database:</strong> {{ config('database.default') }}</p>
            <p><strong>URL:</strong> {{ config('app.url') }}</p>
            <p><strong>Timestamp:</strong> {{ now() }}</p>
        </div>
    </div>
</body>
</html>
