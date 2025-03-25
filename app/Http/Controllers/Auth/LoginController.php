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
        // Previene sesiones fantasmas en caso de errores
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Validación
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'g-recaptcha-response' => ['required', 'captcha']
        ]);

        // Verifica usuario
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Credenciales incorrectas.'
            ])->withInput();
        }

        if (!$user->email_verified) {
            return back()->withErrors([
                'email' => 'Debes verificar tu correo electrónico.'
            ])->withInput();
        }

        // Genera código y lo guarda cifrado
        $plainCode = Str::random(6);
        $hashedCode = Hash::make($plainCode);

        $user->update([
            'verification_code' => $hashedCode,
            'code_expires_at' => now()->addMinutes(15),
        ]);

        // Envia por email
        Mail::to($user->email)->send(new VerificationCodeMail($plainCode));

        // Guarda email temporalmente en sesión
        $request->session()->put('login_email', $user->email);
        $request->session()->save();

        return redirect()->route('login.verification')->with('info', 'Se envió un código de verificación a tu correo.');
    }


    /**
     * Display the verification form where the user can enter the verification code.
     *
     */
    public function showVerificationForm()
    {
        if (!session()->has('login_email')) {
            return redirect()->route('login')->withErrors([
                'error' => 'Por favor, inicia sesión nuevamente.'
            ]);
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
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'g-recaptcha-response' => ['required', 'captcha']
        ]);

        $user = User::where('email', session('login_email'))
                    ->where('code_expires_at', '>', now())
                    ->first();

        if (!$user || !Hash::check($request->code, $user->verification_code)) {
            return back()->withErrors(['code' => 'Código inválido o expirado.']);
        }

        // Limpia sesión vieja y regenera
        session()->flush();
        session()->regenerate();

        // Autenticación manual
        Auth::login($user);

        // Guarda cookie personalizada de sesión
        $response = redirect()->intended(RouteServiceProvider::HOME);
        $response->withCookie(cookie()->forever('user_session', encrypt($user->id)));

        // Limpia código de verificación en DB
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withCookie(cookie()->forget('user_session'));
    }
}