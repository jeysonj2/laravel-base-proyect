<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Changed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
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
        .alert {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
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
            <h1>Password Successfully Changed</h1>
        </div>
        
        <p>Hello {{ $user->name }},</p>
        
        <p>This email confirms that your password has been successfully changed.</p>
        
        <div class="alert">
            <p>If you did not make this change, please contact our support team immediately.</p>
        </div>
        
        <p>Thank you,<br>
        The Support Team</p>
        
        <div class="footer">
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>
