<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        body {
            font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
            line-height: 1.6;
            color: #718096;
            background-color: #edf2f7;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            padding: 25px;
            text-align: center;
        }
        .content {
            padding: 24px 32px;
            background-color: #ffffff;
            margin-bottom: 32px;
        }
        .footer {
            padding: 32px;
            text-align: center;
            font-size: 12px;
            margin-top: 32px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            color: #ffffff !important;
            background-color: #2d3748;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div style="background-color: #edf2f7;">
        <div class="container">
            <div class="header">
                <a href="{{ config('app_link.ema') }}" target="_blank"><img src="https://sso.uiii.ac.id/logo/logo-uiii.png" alt="UIII Logo" height="60"></a>
            </div>
            <div class="content">
                @yield('content')
            </div>
            <div class="footer">
                <p>&copy; {{ date('Y') }} <a href="{{ config('app_link.ema') }}" target="_blank">Universitas Islam Internasional Indonesia</a>. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
