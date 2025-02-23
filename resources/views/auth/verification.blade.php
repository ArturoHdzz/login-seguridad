<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Verification</title>
    <style>
        /* Styles for the verification page container */
        .verification-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
            padding: 20px;
        }

        /* Styles for the verification form */
        .verification-form {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            width: 100%;
            max-width: 450px;
        }

        /* Styles for the verification code input field */
        .verification-code-input {
            letter-spacing: 8px;
            font-size: 24px;
            text-align: center;
        }
    </style>
    <script>
        // Add an event listener to warn the user before leaving the page
        window.addEventListener('beforeunload', (event) => {
            // Show a confirmation dialog
            event.preventDefault(); // Necessary for some browsers
            event.returnValue = ''; // Standard for modern browsers
        });

        // Allow form submission without showing the alert
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('verificationForm');
            form.addEventListener('submit', () => {
                window.removeEventListener('beforeunload', (event) => {});
            });
        });
    </script>
</head>
<body>
    <div class="verification-container">
        <!-- Verification form for user input -->
        <form action="{{ route('login.verify') }}" method="POST" class="verification-form" id="verificationForm">
            @csrf
            <h2>Code Verification</h2>

            <!-- Display validation errors if any -->
            @if($errors->any())
                <div class="error-alert">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <p>We have sent a verification code to your email address.</p>

            <!-- Form group for the verification code input -->
            <div class="form-group">
                <label for="code">Verification Code</label>
                <input 
                    type="text" 
                    id="code" 
                    name="code" 
                    required 
                    maxlength="6"
                    class="verification-code-input"
                    pattern="[0-9A-Za-z]{6}"
                    autocomplete="off"
                >
            </div>

            <!-- Google reCAPTCHA widget -->
            <div class="grupo-formulario">
                <strong>reCAPTCHA</strong>
                {!! NoCaptcha::renderJs() !!}
                {!! NoCaptcha::display() !!}
            </div>

            <!-- Submit button for the verification form -->
            <button type="submit" class="login-button">
                Verify
            </button>
        </form>
    </div>
</body>
</html>
