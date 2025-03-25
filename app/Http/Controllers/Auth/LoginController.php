<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use App\Providers\RouteServiceProvider;

class LoginController extends Controller
{
    /**
     * Show the login form view.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle step 1 of the login process: email/password/captcha validation + code sending.
     */
    public function step1(Request $request)
    {
        $errors = [];

        // validation
        if (!$request->filled('email') || !filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is required and must be valid.';
        }

        if (!$request->filled('password')) {
            $errors[] = 'Password is required.';
        }

        if (!$request->filled('g-recaptcha-response')) {
            $errors[] = 'reCAPTCHA is required.';
        } elseif (!app('captcha')->verifyResponse($request->input('g-recaptcha-response'))) {
            $errors[] = 'Invalid reCAPTCHA.';
        }

        if (!empty($errors)) {
            return redirect()->route('login')
                ->with('login_errors', $errors)
                ->withInput($request->except('password'));
        }

        // Authenticate user
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->route('login')
                ->with('login_errors', ['Invalid credentials.'])
                ->withInput($request->except('password'));
        }

        if (!$user->email_verified) {
            return redirect()->route('login')
                ->with('login_errors', ['You must verify your email before logging in.'])
                ->withInput($request->except('password'));
        }

        // Generate and send verification code
        $plainCode = Str::random(length: 6);
        $user->update([
            'verification_code' => Hash::make($plainCode),
            'code_expires_at' => now()->addMinutes(15),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail($plainCode));

        session(['login_email' => $user->email]);

        return redirect()->route('login.verification')->with('info', 'A verification code has been sent to your email.');
    }

    /**
     * Show the code verification form.
     */
    public function showVerificationForm()
    {
        if (!session()->has('login_email')) {
            return redirect()->route('login')->withErrors([
                'error' => 'Please start the login process again.'
            ]);
        }

        return view('auth.verification');
    }

    /**
     * Handle code verification and login.
     */
    /**
     * Handles verification code input, validates it along with reCAPTCHA, and logs in the user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyCode(Request $request)
    {
        $errors = [];

        // Validate verification code
        if (!$request->filled('code') || !preg_match('/^[0-9A-Za-z]{6}$/', $request->code)) {
            $errors[] = 'The verification code must be exactly 6 alphanumeric characters.';
        }

        // Validate reCAPTCHA
        if (!$request->filled('g-recaptcha-response')) {
            $errors[] = 'Please complete the reCAPTCHA.';
        } elseif (!app('captcha')->verifyResponse($request->input('g-recaptcha-response'))) {
            $errors[] = 'Invalid reCAPTCHA. Please try again.';
        }

        // Return with errors if any
        if (!empty($errors)) {
            return redirect()->route('login.verification')
                ->with('login_errors', $errors)
                ->withInput($request->except('code'));
        }

        // Get user from session
        $user = User::where('email', session('login_email'))
                ->where('code_expires_at', '>', now())
                ->first();

        if (!$user || !Hash::check($request->code, $user->verification_code)) {
            return redirect()->route('login.verification')
                ->with('login_errors', ['Invalid or expired verification code.']);
        }

        // Clear session and regenerate
        session()->flush();
        session()->regenerate(true);

        // Store user session cookie
        $response = redirect()->intended(RouteServiceProvider::HOME);
        $response->withCookie(cookie()->forever('user_session', encrypt($user->id)));

        // Log user in
        Auth::login($user);

        // Clear verification fields
        $user->update([
            'verification_code' => null,
            'code_expires_at' => null,
            'last_login_at' => now(),
        ]);

        return $response;
    }

    /**
     * Logout user and clear session + cookie.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        return redirect()->route('login')
            ->withCookie(cookie()->forget('user_session'));
    }
}