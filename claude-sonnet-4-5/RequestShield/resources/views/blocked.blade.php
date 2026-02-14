<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Forbidden</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .shield-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        h1 {
            color: #2d3748;
            font-size: 36px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .error-code {
            color: #e53e3e;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        p {
            color: #4a5568;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .reason {
            background: #f7fafc;
            border-left: 4px solid #e53e3e;
            padding: 15px;
            margin: 25px 0;
            text-align: left;
            border-radius: 4px;
        }

        .reason strong {
            color: #2d3748;
            display: block;
            margin-bottom: 5px;
        }

        .reason code {
            background: #edf2f7;
            padding: 2px 8px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #e53e3e;
        }

        .timestamp {
            color: #a0aec0;
            font-size: 13px;
            margin-top: 30px;
            font-style: italic;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            color: #718096;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="shield-icon">🛡️</div>
        <h1>Access Forbidden</h1>
        <div class="error-code">Error 403</div>
        
        <p>Your request has been blocked by our security system.</p>
        
        @if(isset($reason))
        <div class="reason">
            <strong>Reason:</strong>
            <code>{{ $reason }}</code>
        </div>
        @endif

        <div class="footer">
            <p>If you believe this is an error, please contact the site administrator.</p>
        </div>

        @if(isset($timestamp))
        <div class="timestamp">
            Blocked at: {{ $timestamp }}
        </div>
        @endif
    </div>
</body>
</html>
