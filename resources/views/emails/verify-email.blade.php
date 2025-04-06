<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h1>Hello, {{ $user->name }}</h1>
    <p>Please click the link below to verify your email address:</p>
    <a href="{{ url('/api/verify-email?code=' . $user->verification_code) }}">Verify Email</a>
    <p>If you did not create an account, no further action is required.</p>
</body>
</html>
