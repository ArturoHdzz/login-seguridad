<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expired Page</title>
    <style>
        /* CSS Reset - removes default browser margins and padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Main body styling
           - Sets the font family
           - Creates a light gray background
           - Sets text color to dark gray
           - Makes the page full height
           - Centers content both horizontally and vertically
        */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #495057;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        /* Container for error content
           - White background with rounded corners
           - Soft shadow for depth
           - Responsive width with maximum constraint
           - Adequate padding for content breathing room
        */
        .error-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 90%;
        }
        
        /* Large error code (404) styling
           - Bold, large text to emphasize the error type
           - Red color to indicate an error state
           - Tight line height to minimize vertical space
        */
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #dc3545;
            line-height: 1;
            margin-bottom: 20px;
        }
        
        /* Error message heading styling
           - Moderate font size for hierarchy
           - Dark color for readability
           - Bottom margin for spacing
        */
        .error-message {
            font-size: 24px;
            margin-bottom: 30px;
            color: #343a40;
        }
        
        /* Description text styling
           - Improved line height for readability
           - Bottom margin for spacing
        */
        .error-description {
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        /* Back button styling
           - Blue background with white text for good contrast
           - Rounded corners and padding for button appearance
           - Smooth transition for hover effect
           - Display as block element with inline behavior
        */
        .back-button {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        /* Hover state for the back button - darkens the blue */
        .back-button:hover {
            background-color: #0b5ed7;
        }
        
        /* Active state for the back button - adds slight movement for feedback */
        .back-button:active {
            transform: translateY(1px);
        }
        
        /* Responsive design for smaller screens
           - Reduces sizes proportionally
           - Triggers at screen width of 576px or less
        */
        @media (max-width: 576px) {
            .error-code {
                font-size: 80px;
            }
            
            .error-message {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Main container for the error content -->
    <div class="error-container">
        <!-- Large 419 error code display -->
        <div class="error-code">419</div>
        
        <!-- Main error message heading -->
        <h1 class="error-message">Expired Page</h1>
        
        <!-- Descriptive text explaining the error -->
        <p class="error-description">Sorry, your session has expired or the security token is invalid.</p>
        
        <!-- Navigation button to return to homepage
             The {{ url('/') }} is a Laravel Blade directive that generates the base URL -->
        <a href="{{ url('/') }}" class="back-button">Go back</a>
    </div>
</body>
</html>