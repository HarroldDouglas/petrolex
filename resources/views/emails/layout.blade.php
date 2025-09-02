<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Notification')</title>
    <style>
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
       .email-header{
           background-color: #227093;
           padding: 15px;
           text-align: center;
       }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }

        .email-footer {
            font-size: 14px;
            color: #777;
            border-top: 1px solid #eee;
            padding: 20px;
        }
        .logo-container img {
            border-radius: 50%;
            background: white;
            height: 40px;
            padding: 5px;
            margin-right: 15px;
        }
        .logo-container {
            display: flex;
            align-items: center; 
            justify-content: flex-start;
            margin-bottom: 15px;
            color: white !important;
        }
        .email-header h2 {
            text-align: left;
            color: white !important;
        }
        .email-body {
            padding: 15px;
        }

        .otp-container {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #227093;
        }

        .otp-code {
            font-size: 32px;
            letter-spacing: 8px;
            color: #227093;
            font-weight: bold;
            display: inline-block;
            padding: 10px 20px;
            background-color: #ffffff;
            border-radius: 6px;
            border: 2px solid #227093;
        }

    </style>
</head>

<body>

    <div class="email-wrapper">
        <div class="email-header">
            <div class="logo-container">
                <img src="{{ asset('assets/images/logo/isogaz-no-bg.png')}}" 
                    alt="Logo" style="height: 40px; margin-right: 15px;">
                <span>{{ config('app.name') }}</span>
            </div>
            <div>
                <h2>@yield('header-title')</h2>
            </div>    
        </div>
        
        <div class="email-body">
            @yield('content')
        </div>
        
        <div class="email-footer">
            @hasSection('footer')
                @yield('footer')
            @else
                <p>{{ __('email.regards') }}</p>
                <p><strong>{{ __('email.team_signature', ['app' => config('app.name')]) }}</strong></p>
            @endif
        </div>
    </div>
    
</body>

</html>
