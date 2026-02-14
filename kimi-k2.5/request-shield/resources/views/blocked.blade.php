<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1a1a2e;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }
        .shield {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #e94560 0%, #ff6b6b 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: pulse 2s ease-in-out infinite;
        }
        .shield::before {
            content: '🛡️';
            font-size: 60px;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff 0%, #e94560 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .status-code {
            font-size: 6rem;
            font-weight: 700;
            color: #e94560;
            margin-bottom: 0.5rem;
            opacity: 0.8;
        }
        p {
            font-size: 1.2rem;
            color: #a0a0a0;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .details {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: left;
        }
        .details h3 {
            color: #e94560;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .details-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.9rem;
        }
        .details-item:last-child {
            border-bottom: none;
        }
        .details-label {
            color: #888;
        }
        .details-value {
            color: #fff;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="shield"></div>
        <div class="status-code">403</div>
        <h1>Access Denied</h1>
        <p>{{ $message }}</p>

        <div class="details">
            <h3>Request Details</h3>
            <div class="details-item">
                <span class="details-label">IP Address:</span>
                <span class="details-value">{{ $ip ?? 'Unknown' }}</span>
            </div>
            <div class="details-item">
                <span class="details-label">User Agent:</span>
                <span class="details-value">{{ $userAgent ?? 'Unknown' }}</span>
            </div>
            <div class="details-item">
                <span class="details-label">Timestamp:</span>
                <span class="details-value">{{ now()->format('Y-m-d H:i:s') }}</span>
            </div>
        </div>
    </div>
</body>
</html>