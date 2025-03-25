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
        $errors = [];

        // Validación manual sin lanzar excepción
        if (!$request->filled('email') || !filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email es obligatorio y debe ser válido.';
        }

        if (!$request->filled('password')) {
            $errors[] = 'La contraseña es obligatoria.';
        }

        if (!$request->filled('g-recaptcha-response')) {
            $errors[] = 'Debe resolver el reCAPTCHA.';
        } elseif (!app('captcha')->verifyResponse($request->input('g-recaptcha-response'))) {
            $errors[] = 'El reCAPTCHA no fue válido.';
        }        

        // Si hay errores, retornar con una variable de sesión personalizada
        if (!empty($errors)) {
            return redirect()->route('login')
                ->with('login_errors', $errors)
                ->withInput($request->except('password'));
        }

        // Validar usuario
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->route('login')
                ->with('login_errors', ['Las credenciales no coinciden con nuestros registros.'])
                ->withInput($request->except('password'));
        }

        if (!$user->email_verified) {
            return redirect()->route('login')
                ->with('login_errors', ['Debes verificar tu correo electrónico antes de iniciar sesión.'])
                ->withInput($request->except('password'));
        }

        // Código de verificación
        $plainCode = Str::random(6);
        $user->update([
            'verification_code' => Hash::make($plainCode),
            'code_expires_at' => now()->addMinutes(15),
        ]);

        // Enviar correo
        Mail::to($user->email)->send(new VerificationCodeMail($plainCode));

        // Guardar sesión y redirigir
        session(['login_email' => $user->email]);

        return redirect()->route('login.verification')->with('info', 'Se envió un código de verificación a tu correo.');
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