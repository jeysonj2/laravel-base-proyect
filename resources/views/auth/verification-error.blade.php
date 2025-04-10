<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Email Verification Failed</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-top: 40px;
        }
        .error-icon {
            color: #f44336;
            font-size: 48px;
            margin-bottom: 20px;
        }
        h1 {
            color: #f44336;
            margin-bottom: 20px;
        }
        p {
            margin-bottom: 16px;
        }
        .btn {
            display: inline-block;
            background-color: #2196F3;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0b7dda;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="error-icon">✕</div>
        <h1>Email Verification Failed</h1>
        <p>{{ $message ?? 'There was an error verifying your email address.' }}</p>
        <p>This could be due to:</p>
        <ul style="text-align: left; display: inline-block;">
            <li>An invalid or expired verification code</li>
            <li>An email that has already been verified</li>
            <li>A technical error in our system</li>
        </ul>
        <p>If you believe this is a mistake, you can try to:</p>
        <a href="/" class="btn">Go to Homepage</a>
    </div>
</body>
</html>
