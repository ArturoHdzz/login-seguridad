<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Mail\VerificationMail;
use App\Mail\EmailVerification;

class RegisterController extends Controller
{
    // Show the registration form
    public function showRegistrationForm()
    {
        // Asegúrate que la sesión esté limpia si entran directo
        session()->flush();
    
        // Borra la cookie si aún está
        return response()->view('auth.register')->withCookie(cookie()->forget('user_session'));
    }

    // Handle registration and send email verification
    public function register(Request $request)
    {
        // Validate the input data
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[A-Za-zÁ-ÿ\s]+$/'],
            'email' => ['required', 'email', 'unique:users,email', 'max:255', 'regex:/^([a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.com))$/'],
            'password' => [
                'required', 
                'min:8', 
                'confirmed', 
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
            'g-recaptcha-response' => ['required', 'captcha']
        ]);
    
        try {
            // Create the user without verification
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => $validatedData['password'],
            ]);
    
            // Generate a signed URL that expires in 24 hours
            $url = URL::temporarySignedRoute(
                'verification.email',
                now()->addHours(24),
                ['id' => $user->id]
            );
    
            // Send the verification email
            Mail::to($user->email)->send(new EmailVerification($url, $user->name));
    
            // Redirect with a detailed success message
            return redirect()->route('login')->with('success', 
                'Registration successful! We have sent a verification email to ' . $user->email . 
                ' with a link to verify your account. Please check your inbox and spam folder. ' .
                'The link will expire in 24 hours.'
            );
    
        } catch (\Exception $e) {
            // Handle errors while sending the email
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'There was an issue sending the verification email. Please try again.']);
        }
    }

    // Verify email through the provided signed URL
    public function verifyEmail(Request $request, $id)
    {
        // Check if the URL is valid and not expired
        if (!$request->hasValidSignature()) {
            return view('auth.verification-result', [
                'status' => 'error',
                'message' => 'The verification link has expired or is invalid.'
            ]);
        }
    
        $user = User::findOrFail($id);
    
        // Check if the user is already verified
        if ($user->email_verified) {
            return view('auth.verification-result', [
                'status' => 'info',
                'message' => 'This account has already been verified.'
            ]);
        }
    
        // Mark the email as verified
        $user->update([
            'email_verified' => true
        ]);
    
        return view('auth.verification-result', [
            'status' => 'success',
            'message' => 'Your account has been successfully verified! You can now log in.'
        ]);
    }

    // Handle the email verification process after user inputs the verification code
    public function verifyRegistration(Request $request)
    {
        // Validate the verification code
        $request->validate([
            'code' => ['required', 'string', 'size:6']
        ]);

        $email = session('verify_email');
        if (!$email) {
            return redirect()->route('register.show');
        }

        $user = User::where('email', $email)
                      ->where('email_verified', false)
                      ->where('code_expires_at', '>', now())
                      ->first();

        if (!$user) {
            return back()->withErrors(['code' => 'User not found or code expired.']);
        }

        // Verify the hashed code
        if (!Hash::check($request->code, $user->verification_code)) {
            return back()->withErrors(['code' => 'Invalid code.']);
        }

        // Mark the email as verified
        $user->update([
            'email_verified' => true,
            'verification_code' => null,
            'code_expires_at' => null
        ]);

        session()->forget('verify_email');
        return redirect()->route('login')->with('success', 'Email successfully verified. You can now log in.');
    }
}
