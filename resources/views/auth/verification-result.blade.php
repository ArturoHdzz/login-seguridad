<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification</title>
    <style>
        /* Main container styling */
        .verification-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
            padding: 20px;
        }

        /* Message box styling */
        .verification-message {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        /* Icon styling */
        .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .success {
            color: #28a745;
        }

        .error {
            color: #dc3545;
        }

        .info {
            color: #17a2b8;
        }

        /* Message text styling */
        .message {
            color: #333;
            margin-bottom: 20px;
        }

        /* Login button styling */
        .login-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .login-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-message">
            <!-- Dynamic status icon based on verification result -->
            @if($status === 'success')
                <div class="icon success">✓</div>
            @elseif($status === 'error')
                <div class="icon error">✕</div>
            @else
                <div class="icon info">ℹ</div>
            @endif

            <!-- Display the verification message -->
            <h2 class="message">{{ $message }}</h2>

            <!-- Link to the login page -->
            <a href="{{ route('login') }}" class="login-button">
                Go to Login
            </a>
        </div>
    </div>
</body>
</html>
