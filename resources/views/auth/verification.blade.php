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

        /* Form group styling */
        .form-group {
            margin-bottom: 15px;
        }

        /* Label styling */
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        /* Styles for the verification code input field */
        .verification-code-input {
            width: 100%;
            padding: 10px;
            letter-spacing: 8px;
            font-size: 24px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        /* Error alert styling */
        .error-alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        /* Verify button styling */
        .verify-button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 15px;
        }

        .verify-button:hover {
            background-color: #0056b3;
        }

        /* Loading spinner animation */
        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }

        /* Spinner styling */
        .spinner-border {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 0.2em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border 0.75s linear infinite;
            margin-right: 8px;
        }

        /* Hide screen reader text */
        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <!-- Verification form for user input -->
        <form action="{{ route('login.verify') }}" method="POST" class="verification-form" id="verificationForm">
            @csrf
            <h2>Code Verification 2</h2>

            <!-- Display validation errors if any -->
            @if(session('login_errors'))
                <div class="error-alert">
                    @foreach(session('login_errors') as $error)
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
            <div class="form-group">
                <strong>reCAPTCHA</strong>
                {!! NoCaptcha::renderJs() !!}
                {!! NoCaptcha::display() !!}
            </div>

            <!-- Submit button for the verification form -->
            <button type="submit" class="verify-button" id="verify-button">
                Verify
            </button>
        </form>
    </div>
    <script>
        // Define the beforeUnload handler first
        const beforeUnloadHandler = (event) => {
            event.preventDefault();
            event.returnValue = '';
        };

        // Add the event listener
        window.addEventListener('beforeunload', beforeUnloadHandler);

        // Handle form submission
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('verificationForm');
            const submitButton = document.getElementById('verify-button');
            
            form.addEventListener('submit', () => {
                // Disable button and show loading status
                submitButton.disabled = true;
                submitButton.innerHTML = `
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div> Verifying...
                `;

                // Remove the beforeunload event when submitting the form
                window.removeEventListener('beforeunload', beforeUnloadHandler);
            });
        });

        // Restore button state when navigating back
        window.addEventListener('pageshow', (event) => {
            if (event.persisted || performance.navigation.type === 2) {
                const submitButton = document.getElementById('verify-button');
                submitButton.disabled = false;
                submitButton.innerHTML = 'Verify';
                
                // Re-add the beforeunload event
                window.addEventListener('beforeunload', beforeUnloadHandler);
            }
        });
    </script>
</body>
</html>