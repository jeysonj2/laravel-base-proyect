<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Locked</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            border-top: 4px solid #dc3545;
        }
        h1 {
            color: #dc3545;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Account Locked</h1>
        
        <p>Hello {{ $name }},</p>
        
        @if($isPermanent)
            <h2>Your Account Has Been Permanently Locked</h2>
            <p>Your account has been <strong>permanently locked</strong> due to multiple failed login attempts.</p>
            <p>To regain access to your account, please contact an administrator.</p>
        @else
            <h2>Your Account Has Been Temporarily Locked</h2>
            <p>Your account has been temporarily locked due to multiple failed login attempts.</p>
            <p>Your account will be automatically unlocked in <strong>{{ $lockoutDuration }} {{ Str::plural('minute', $lockoutDuration) }}</strong>.</p>
            <p>If you need immediate access to your account, please contact an administrator.</p>
        @endif
        
        <p>If you did not attempt to log in to your account, we recommend changing your password immediately after you regain access.</p>
        
        <div class="footer">
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>
