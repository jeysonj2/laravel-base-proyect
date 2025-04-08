<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .token {
            background-color: #f5f5f5;
            padding: 10px;
            text-align: center;
            font-size: 16px;
            letter-spacing: 2px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reset Your Password</h1>
        </div>
        
        <p>Hello {{ $user->name }},</p>
        
        <p>We received a request to reset your password. If you didn't make this request, you can safely ignore this email.</p>
        
        <p>To reset your password, please use the following token:</p>
        
        <div class="token">
            {{ $token }}
        </div>
        
        <p>This token will expire in {{ $expiryMinutes }} {{ Str::plural('minute', $expiryMinutes) }}.</p>
        
        <p>Thank you,<br>
        The Support Team</p>
        
        <div class="footer">
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>
