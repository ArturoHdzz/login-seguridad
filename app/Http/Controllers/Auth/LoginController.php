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
     * Display the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return response()
        ->view('auth.login')
        ->withCookie(cookie()->forget('user_session'));
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
        // Validación de campos con reCAPTCHA
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'g-recaptcha-response' => ['required', 'captcha']
        ]);
    
        // Buscar usuario
        $user = User::where('email', $credentials['email'])->first();
    
        // Verificación de credenciales
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'The credentials do not match our records.'])->withInput();
        }
    
        // Verificación de correo
        if (!$user->email_verified) {
            return back()->withErrors(['email' => 'You must verify your email address before logging in.'])->withInput();
        }
    
        // Importante: NO iniciar sesión aquí ni crear cookie personalizada
    
        // Generar código de verificación
        $plainCode = Str::random(6);
        $hashedCode = Hash::make($plainCode);
    
        $user->update([
            'verification_code' => $hashedCode,
            'code_expires_at' => now()->addMinutes(15)
        ]);
    
        // Enviar correo
        Mail::to($user->email)->send(new VerificationCodeMail($plainCode));
    
        // Guardar el email temporalmente en sesión para el paso 2
        $request->session()->put('login_email', $user->email);
        $request->session()->save();
    
        // Redirigir al formulario de verificación
        return redirect()
        ->route('login.verification')
        ->with('info', 'A verification code has been sent to your email.')
        ->withCookie(cookie()->forget('user_session'));
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
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'g-recaptcha-response' => ['required', 'captcha']
        ]);
    
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