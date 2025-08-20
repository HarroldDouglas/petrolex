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
            <p>Cordialement,</p>
            <p><strong>L'équipe Petrolex</strong></p>
        </div>
    </div>
    
</body>

</html>
