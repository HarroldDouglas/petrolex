<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Your Authentication Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }

        .otp-container {
            text-align: center;
            margin: 30px 0;
        }

        .otp-code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
    </style>
</head>

<body>
    <h2>Authentication Code</h2>
    <p>Hello,</p>
    <p>We received a request to verify your identity for {{ $maskedIdentifier }}. Please use the following code to
        complete the process:</p>

    <div class="otp-container">
        <div class="otp-code">{{ $otp }}</div>
    </div>

    <p>This code will expire in 10 minutes for security reasons.</p>
    <p>If you didn't request this code, please ignore this email or contact our support team if you have concerns.</p>

    <div class="footer">
        <p>This is an automated message, please do not reply to this email.</p>
    </div>
</body>

</html>
