<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unsubscribed</title>
    <link rel="stylesheet" href="{{ url('assets/css/theme.min.css') }}">
    <style>
        body {
            background: #f6f8fb;
        }

        .unsubscribe-shell {
            align-items: center;
            display: flex;
            min-height: 100vh;
            padding: 24px;
        }

        .unsubscribe-panel {
            background: #fff;
            border: 1px solid #e7ecf3;
            border-radius: 8px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
            margin: 0 auto;
            max-width: 520px;
            padding: 32px;
            text-align: center;
        }
    </style>
</head>

<body>
    <main class="unsubscribe-shell">
        <div class="unsubscribe-panel">
            <h1 class="h3">You are unsubscribed</h1>
            <p class="text-muted mb-0">{{ $email }} will no longer receive marketing emails.</p>
        </div>
    </main>
</body>

</html>
