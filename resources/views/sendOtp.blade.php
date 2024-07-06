<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: auto;
            border: 1px solid #e0e0e0;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 10px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>Barbar</h1>
    </div>
    <div class="content">

        <h1>Send Otp</h1>

        <h3>Email from: <a href="barbar@gmail.com">barbar@gmail.com</a></h3>
        <h3>Your one time Code is <strong style="color: #007bff">{{ $otp }}</strong></h3>
        <h3>Thank you</h3>

        <p>If you have any questions, feel free to <a href="#">contact our support team</a>.</p>
        <p>Best regards,</p>
        <p>Barbar Team</p>
    </div>
    <div class="footer">
        <p>&copy; 2024 Barbar. All rights reserved.</p>
        <p><a href="#">Unsubscribe</a> | <a href="#">Privacy Policy</a></p>
    </div>
</div>
</body>
</html>













{{--<h1>Email from: barbar@gmail.com</h1>--}}
{{--    <h1>Your one time Code is {{ $otp }}</h1>--}}
{{--    <h2>Thank you</h2>--}}
