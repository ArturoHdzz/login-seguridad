<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <style>
        /* Container styling for the registration form */
        .registration-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
            padding: 20px;
        }

        /* Styling for the form */
        .registration-form {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 450px;
        }

        /* Form group styling */
        .form-group {
            margin-bottom: 15px;
        }

        /* Label styling for input fields */
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        /* Input field styling */
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        /* Input field focus effect */
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
        }

        /* Styling for the registration button */
        .register-button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        /* Hover effect for the registration button */
        .register-button:hover {
            background-color: #0056b3;
        }

        /* Error alert styling */
        .error-alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        /* Small text styling */
        small {
            color: #666;
            display: block;
            margin-top: 5px;
        }

        /* Heading styling */
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <form id="registration-form" action="{{ route('register') }}" method="POST" class="registration-form">
            @csrf
            <h2>Registration 2</h2>

            <!-- Display errors if any -->
            @if(session('success'))
                <div class="success-alert">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('register_errors'))
                <div class="alert alert-danger">
                    <ul>
                        @foreach(session('register_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @error('system_error')
                <div class="error-alert">
                    {{ $message }}
                </div>
            @enderror

            <!-- Full Name Input -->
            <div class="form-group">
                <label for="full-name">Full Name</label>
                <input 
                    type="text" 
                    id="full-name" 
                    name="name" 
                    required 
                    minlength="3" 
                    maxlength="100"
                    pattern="[A-Za-zÁ-ÿ\s]+"
                    value="{{ old('name') }}"
                >
            </div>

            <!-- Email Input -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    value="{{ old('email') }}"
                >
            </div>

            <!-- Password Input -->
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    minlength="8"
                    pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$"
                    title="Password must have at least 8 characters, one uppercase, one lowercase, one number, and one special character"
                >
                <small>Minimum 8 characters, one uppercase, one lowercase, one number, and one special character (@$!%*?&#)</small>
            </div>

            <!-- Password Confirmation Input -->
            <div class="form-group">
                <label for="password-confirmation">Confirm Password</label>
                <input 
                    type="password" 
                    id="password-confirmation" 
                    name="password_confirmation" 
                    required
                >
            </div>

            <!-- reCAPTCHA Integration -->
            <div class="form-group">
                <strong>reCAPTCHA</strong>
                {!! NoCaptcha::renderJs() !!}
                {!! NoCaptcha::display() !!}
            </div>

            <!-- Submit Button -->
            <button type="submit" id="submit-button" class="register-button">
                Register
            </button>
        </form>
    </div>
    <script>
    document.getElementById('registration-form').addEventListener('submit', function(e) {
        const submitButton = document.getElementById('submit-button');
        const originalText = submitButton.innerHTML;
        
        // Disable and show spinner
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;

        // Re-enable if validation error
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || performance.navigation.type === 2) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
        });
    });
    </script>
</body>
</html>
