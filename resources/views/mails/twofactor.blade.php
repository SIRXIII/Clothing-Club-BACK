<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Two-Factor Authentication Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #F77F00;
            margin-bottom: 10px;
        }
        .content {
            margin-bottom: 30px;
        }
        .code-container {
            background-color: #f8f9fa;
            border: 2px solid #F77F00;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            color: #F77F00;
            letter-spacing: 4px;
            font-family: 'Courier New', monospace;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .security-info {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            color: #0056b3;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Travel Clothing Club</div>
            <h2>Two-Factor Authentication Code</h2>
        </div>

        <div class="content">
            <p>Hello {{ $user->first_name ?? 'Partner' }},</p>

            <p>You're attempting to sign in to your Travel Clothing Club Admin account. To complete your login, please use the verification code below:</p>

            <div class="code-container">
                <div class="code">{{ $code }}</div>
            </div>

            <div class="warning">
                <strong>Important:</strong> This verification code will expire in 10 minutes for security reasons.
            </div>

            <div class="security-info">
                <strong>Security Notice:</strong> If you didn't attempt to sign in, please ignore this email and consider changing your password immediately.
            </div>

            <p>Simply enter this code in the verification screen to complete your login process.</p>

            <p>For your security, never share this code with anyone. Our team will never ask you for this code via email, phone, or any other method.</p>
        </div>

        <div class="footer">
            <p>This email was sent from Travel Clothing Club Partner Panel</p>
            <p>If you have any questions, please contact our support team.</p>
            <p>&copy; {{ date('Y') }} Travel Clothing Club. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
