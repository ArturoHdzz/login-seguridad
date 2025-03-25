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
    /**
     * Display the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration form submission and send email verification link.
     */
    public function register(Request $request)
    {
        $errors = [];

        // Manual input validation
        if (!$request->filled('name') || !preg_match('/^[A-Za-zÁ-ÿ\s]+$/', $request->name)) {
            $errors[] = 'Name is required and should only contain letters.';
        }

        if (!$request->filled('email') || !filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is required and must be valid.';
        } elseif (!preg_match('/^([a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.com))$/', $request->email)) {
            $errors[] = 'Only Gmail and Hotmail addresses are allowed.';
        } elseif (User::where('email', $request->email)->exists()) {
            $errors[] = 'This email is already registered.';
        }

        if (!$request->filled('password')) {
            $errors[] = 'Password is required.';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/', $request->password)) {
            $errors[] = 'Password must contain at least 8 characters including an uppercase letter, lowercase letter, number, and a special character.';
        }

        if ($request->password !== $request->password_confirmation) {
            $errors[] = 'Password confirmation does not match.';
        }

        if (!$request->filled('g-recaptcha-response')) {
            $errors[] = 'reCAPTCHA is required.';
        } elseif (!app('captcha')->verifyResponse($request->input('g-recaptcha-response'))) {
            $errors[] = 'Invalid reCAPTCHA.';
        }

        // Return with errors if any
        if (!empty($errors)) {
            return redirect()->route('register.show')
                ->with('register_errors', $errors)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            // Generate signed URL for email verification (expires in 24h)
            $url = URL::temporarySignedRoute(
                'verification.email',
                now()->addHours(24),
                ['id' => $user->id]
            );

            // Send email with link
            Mail::to($user->email)->send(new EmailVerification($url, $user->name));

            return redirect()->route('login')->with('success',
                'Registration successful! We sent a verification email to ' . $user->email .
                '. Please check your inbox or spam folder. The link will expire in 24 hours.'
            );

        } catch (\Exception $e) {
            return redirect()->route('register.show')
                ->with('register_errors', ['There was a problem sending the verification email. Please try again.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Handle email verification via signed link.
     */
    public function verifyEmail(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            return view('auth.verification-result', [
                'status' => 'error',
                'message' => 'The verification link has expired or is invalid.'
            ]);
        }

        $user = User::findOrFail($id);

        if ($user->email_verified) {
            return view('auth.verification-result', [
                'status' => 'info',
                'message' => 'This account has already been verified.'
            ]);
        }

        $user->update(['email_verified' => true]);

        return view('auth.verification-result', [
            'status' => 'success',
            'message' => 'Your account has been successfully verified! You can now log in.'
        ]);
    }

    /**
     * Handle code verification for registration (if used).
     */
    public function verifyRegistration(Request $request)
    {
        $errors = [];

        if (!$request->filled('code') || strlen($request->code) !== 6) {
            $errors[] = 'The code must be exactly 6 characters.';
        }

        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        $email = session('verify_email');
        if (!$email) {
            return redirect()->route('register.show');
        }

        $user = User::where('email', $email)
                    ->where('email_verified', false)
                    ->where('code_expires_at', '>', now())
                    ->first();

        if (!$user) {
            return back()->withErrors(['code' => 'User not found or code has expired.']);
        }

        if (!Hash::check($request->code, $user->verification_code)) {
            return back()->withErrors(['code' => 'Invalid code.']);
        }

        $user->update([
            'email_verified' => true,
            'verification_code' => null,
            'code_expires_at' => null
        ]);

        session()->forget('verify_email');

        return redirect()->route('login')->with('success', 'Email successfully verified. You can now log in.');
    }
}
