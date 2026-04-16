<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1A1208;
            background-color: #FAF6F0;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #C44B2B 0%, #A03D23 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 800;
        }
        .header .emoji {
            font-size: 48px;
            margin-bottom: 16px;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1A1208;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            color: #4A4A4A;
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .code-container {
            background: #FAF6F0;
            border: 3px solid #C44B2B;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .code-label {
            font-size: 14px;
            color: #9C8875;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .code {
            font-size: 48px;
            font-weight: 800;
            color: #C44B2B;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .expiry {
            font-size: 13px;
            color: #9C8875;
            margin-top: 12px;
        }
        .warning {
            background: #FFF5F0;
            border-left: 4px solid #C44B2B;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning p {
            margin: 0;
            font-size: 14px;
            color: #4A4A4A;
        }
        .footer {
            background: #FAF6F0;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #EDE0CE;
        }
        .footer p {
            margin: 0;
            font-size: 13px;
            color: #9C8875;
        }
        .footer .brand {
            font-weight: 700;
            color: #C44B2B;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="emoji">👪</div>
            <h1>Verify Your Account</h1>
        </div>
        
        <div class="content">
            <div class="greeting">Hello {{ $userName }}!</div>
            
            <div class="message">
                Welcome to <strong>Paulette Culture Kids</strong>! We're excited to have you join our community.
                <br><br>
                To complete your registration and start your children's cultural learning journey, please verify your email address using the code below:
            </div>
            
            <div class="code-container">
                <div class="code-label">Your Verification Code</div>
                <div class="code">{{ $code }}</div>
                <div class="expiry">⏰ Expires at {{ $expiresAt }}</div>
            </div>
            
            <div class="warning">
                <p><strong>🔒 Security Note:</strong> If you didn't create an account with CultureKids, please ignore this email. Your security is important to us.</p>
            </div>
            
            <div class="message">
                Once verified, you'll be able to:
                <ul style="color: #4A4A4A; margin: 16px 0;">
                    <li>Create profiles for your children</li>
                    <li>Access culturally rich stories and activities</li>
                    <li>Track your children's learning progress</li>
                    <li>Explore Uganda's heritage heroes</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>This code will expire in 15 minutes.</p>
            <p class="brand">Paulette Culture Kids</p>
            <p>Discover Uganda's Heritage Heroes 🌍</p>
        </div>
    </div>
</body>
</html>
