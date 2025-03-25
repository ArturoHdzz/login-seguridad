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
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Display the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle the first step of the login process where the user's credentials are validated.
     * If valid, a verification code is sent to the user's email.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function step1(Request $request)
    {
        // Validate email and password from the login form
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
            'g-recaptcha-response' => ['required', 'captcha'],
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator->errors())->withInput();
        }
        
        $credentials = $validator->validated();
    
        // Check if the user exists and if the password matches
        $user = User::where('email', $credentials['email'])->first();
    
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'The credentials do not match our records.'
            ])->withInput();
        }
    
        // Ensure the user has verified their email before logging in
        if (!$user->email_verified) {
            return back()->withErrors([
                'email' => 'You must verify your email address before logging in.'
            ])->withInput();
        }
    
        // Generate a random verification code
        $plainCode = Str::random(6);
        $hashedCode = Hash::make($plainCode);
    
        // Update the user with the hashed verification code and expiration time (15 minutes)
        $user->update([
            'verification_code' => $hashedCode,
            'code_expires_at' => now()->addMinutes(15)
        ]);
     
        // Send the code to the user's email
        Mail::to($user->email)->send(new VerificationCodeMail($plainCode));
    
        // Store the user's email in the session
        $request->session()->put('login_email', $user->email);
        $request->session()->save();
    
        // Ensure the session is saved before redirecting
        return redirect()->route('login.verification')->with('info', 'A verification code has been sent to your email.');
    }

    /**
     * Display the verification form where the user can enter the verification code.
     *
     */
    public function showVerificationForm()
    {
        // Check if the email exists in the session
        if (!session()->has('login_email')) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Please log in again.']);
        }

        return view('auth.verification');
    }

    /**
     * Verify the provided code and log the user in if valid.
     * If valid, clears the session and regenerates it, creates a persistent user session cookie, 
     * and logs the user in manually.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyCode(Request $request)
    {
        // Validate that the code is a string of exactly 6 characters
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'size:6'],
            'g-recaptcha-response' => ['required', 'captcha'],
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator->errors())->withInput();
        }
    
        // Find the user by email and ensure the code is not expired
        $user = User::where('email', session('login_email'))
                   ->where('code_expires_at', '>', now())
                   ->first();
    
        if (!$user || !Hash::check($request->code, $user->verification_code)) {
            return back()->withErrors([
                'code' => 'Invalid or expired code.'
            ]);
        }
    
        // Clear the temporary session data
        session()->flush();
        session()->regenerate(true);
    
        // Create a persistent session cookie for the user
        $response = redirect()->intended(RouteServiceProvider::HOME);
        $response->withCookie(cookie()->forever('user_session', encrypt($user->id)));
    
        // Log the user in manually
        Auth::login($user);
    
        // Update the user’s record by clearing the verification code and expiration time
        $user->update([
            'verification_code' => null,
            'code_expires_at' => null,
            'last_login_at' => now()
        ]);
    
        return $response;
    }

    /**
     * Log the user out, clear the session and cookie.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Log the user out manually
        Auth::logout();
    
        // Remove the 'user_session' cookie
        $response = redirect()->route('login')
            ->withCookie(cookie()->forget('user_session'));
    
        return $response;
    }
}