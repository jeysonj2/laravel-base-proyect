<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h1>Verify Your Email Address</h1>
    <p>Hello, {{ $user->name }}</p>
    <p>Please click the link below to verify your email address:</p>
    @php
        $verificationUrl = config('verification.email_verification_url') 
            ? config('verification.email_verification_url') . '?code=' . $user->verification_code 
            : url('/verify-email?code=' . $user->verification_code);
    @endphp
    <a href="{{ $verificationUrl }}">Verify Email</a>
    <p>If you did not create an account, no further action is required.</p>
</body>
</html>
