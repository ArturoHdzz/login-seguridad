<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Styles for the login container */
        .contenedor-login {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
            padding: 20px;
        }

        /* Styles for the login form */
        .formulario-login {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            width: 100%;
            max-width: 450px;
        }

        /* Group styling for form fields */
        .grupo-formulario {
            margin-bottom: 15px;
        }

        .grupo-formulario label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .grupo-formulario input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        .grupo-formulario input:focus {
            outline: none;
            border-color: #007bff;
        }

        /* Login button styling */
        .boton-login {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .boton-login:hover {
            background-color: #0056b3;
        }

        /* Error message styling */
        .alerta-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        /* Registration link styling */
        .enlace-registro {
            text-align: center;
            margin-top: 15px;
        }

        .alerta {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Success and error alert styling */
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.2em;
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
    <div class="contenedor-login">
        <!-- Login form that submits to the 'login.paso1' route -->
        <form id="login-form" action="{{ route('login.step1') }}" method="POST" class="formulario-login">
            @csrf
            <!-- Display success message -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Display error message -->
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Display validation errors -->
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h2>Login 2</h2>

            <!-- Email input -->
            <div class="grupo-formulario">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    value="{{ old('email') }}"
                >
            </div>

            <!-- Password input -->
            <div class="grupo-formulario">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                >
            </div>

            <!-- Google reCAPTCHA widget -->
            <div class="grupo-formulario">
                <strong>reCAPTCHA</strong>
                {!! NoCaptcha::renderJs() !!}
                {!! NoCaptcha::display() !!}
            </div>

            <!-- Submit button -->
            <button type="submit" id="submit-button" class="boton-login">
                Continue
            </button>

            <!-- Registration link -->
            <div class="enlace-registro">
                <a href="{{ route('register.show') }}" id="register-link">Don't have an account? Register</a>
            </div>
        </form>
    </div>
    <script>
        // Store the original button text to restore it if needed
        const submitButton = document.getElementById('submit-button');
        const originalButtonText = submitButton.innerHTML;

        // Handle form submission
        document.getElementById('login-form').addEventListener('submit', function(e) {
            // Disable button and show loading spinner
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div> Processing...
            `;
        });

        // Handle registration link click
        document.getElementById('register-link').addEventListener('click', function(e) {
            // Disable the link to prevent double-clicks
            this.style.pointerEvents = 'none';
            this.style.cursor = 'not-allowed';
            this.style.opacity = '0.5';
            
            // Manually redirect after 300ms to give visual feedback
            setTimeout(() => {
                window.location.href = this.href;
            }, 300);
        });

        // Restore button state when navigating back
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || performance.navigation.type === 2) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    </script>
</body>
</html>
