<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard</title>
    <style>
        /* Container for the dashboard, centered with a max-width of 1200px */
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Top bar with a background color, shadow, and some padding */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border-radius: 8px;
        }

        /* Text style for the welcome message */
        .welcome-text {
            font-size: 1.2rem;
            color: #333;
        }

        /* Style for the logout form, no margin */
        .logout-form {
            margin: 0;
        }

        /* Logout button with background color, padding, and transition effect */
        .logout-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }

        /* Change background color when hovering over the logout button */
        .logout-button:hover {
            background-color: #c82333;
        }

        /* Main content area with background color, padding, and shadow */
        .main-content {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Ensure the background of the body is light gray */
        body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
    </style>
</head>
<body>
    <!-- Main container for the dashboard content -->
    <div class="dashboard-container">
        
        <!-- Display error message if session contains an error -->
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Top bar displaying the user's name and a logout button -->
        <div class="top-bar">
            <span class="welcome-text">
                Welcome, {{ Auth::user()->name }}
            </span>
            <!-- Logout form with POST method to log out -->
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-button">
                    Log Out
                </button>
            </form>
        </div>

        <!-- Main content area for the dashboard -->
        <div class="main-content">
            <h1>Dashboard 2</h1>
        </div>
    </div>

    <script>
        // Prevent double submission of the logout form
        document.querySelector('.logout-form').addEventListener('submit', function(e) {
            const button = this.querySelector('button');
            if (button.disabled) {
                e.preventDefault();
                return false;
            }
            button.disabled = true;
        });
    </script>
</body>
</html>
