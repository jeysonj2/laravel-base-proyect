<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Email Verification Successful</title>
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
        .success-icon {
            color: #4CAF50;
            font-size: 48px;
            margin-bottom: 20px;
        }
        h1 {
            color: #4CAF50;
            margin-bottom: 20px;
        }
        p {
            margin-bottom: 16px;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="success-icon">✓</div>
        <h1>Email Verification Successful</h1>
        <p>Hello {{ $user->name }},</p>
        <p>Your email address <strong>{{ $user->email }}</strong> has been successfully verified.</p>
        <p>Your account is now fully activated and you can access all features of the application.</p>
        
        <a href="/" class="btn">Go to Homepage</a>
    </div>
</body>
</html>
