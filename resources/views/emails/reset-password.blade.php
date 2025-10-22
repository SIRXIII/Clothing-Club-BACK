<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
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
        .button {
            display: inline-block;
            background-color: #F77F00;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #e66f00;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Travel Clothing Club</div>
            <h2>Reset Your Password</h2>
        </div>

        <div class="content">
            <p>Hello {{ $user->name ?? 'Partner' }},</p>
            
            <p>We received a request to reset your password for your Travel Clothing Club Partner account. If you made this request, click the button below to reset your password:</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button" style="color: white;">Reset Password</a>
            </div>
            
            <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
            <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px;">
                {{ $resetUrl }}
            </p>
            
            <div class="warning">
                <strong>Important:</strong> This password reset link will expire in 24 hours for security reasons.
            </div>
            
            <p>If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>
            
            <p>For security reasons, if you continue to receive these emails without requesting them, please contact our support team immediately.</p>
        </div>

        <div class="footer">
            <p>This email was sent from Travel Clothing Club Admin Panel</p>
            <p>If you have any questions, please contact our support team.</p>
            <p>&copy; {{ date('Y') }} Travel Clothing Club. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
