<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <!-- Main container for the email content, centered with a max-width of 600px -->
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        
        <h2>Hello {{ $name }}!</h2>
        
        <p>Thank you for registering. Please verify your account by clicking the link below:</p>
        
        <!-- Verification button with a link to the verification URL -->
        <div style="margin: 25px 0;">
            <a href="{{ $url }}" 
               style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                Verify My Account
            </a>
        </div>
        
        <p>This link will expire in 24 hours for security reasons.</p>
        
        <p>If you did not create this account, you can ignore this email.</p>
    </div>
</body>
</html>
