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
    public function verifyCode(Request $request)
    {
        $errors = [];

        if (!$request->filled('code') || strlen($request->code) !== 6) {
            $errors[] = 'The verification code must be exactly 6 characters.';
        }

        if (!$request->filled('g-recaptcha-response')) {
            $errors[] = 'reCAPTCHA is required.';
        } elseif (!app('captcha')->verifyResponse($request->input('g-recaptcha-response'))) {
            $errors[] = 'Invalid reCAPTCHA.';
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->withErrors($errors)
                ->withInput();
        }

        $user = User::where('email', session('login_email'))
            ->where('code_expires_at', '>', now())
            ->first();

        if (!$user || !Hash::check($request->code, $user->verification_code)) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        // Clear session and regenerate
        session()->flush();
        session()->regenerate(true);

        Auth::login($user);
        $user->update([
            'verification_code' => null,
            'code_expires_at' => null,
            'last_login_at' => now(),
        ]);

        return redirect()->intended(RouteServiceProvider::HOME)
            ->withCookie(cookie()->forever('user_session', encrypt($user->id)));
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