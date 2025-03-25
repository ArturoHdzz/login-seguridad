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
use Anhskohbo\NoCaptcha\Facades\NoCaptcha;

class RegisterController extends Controller
{
    // Show the registration form
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Handle registration and send email verification
    public function register(Request $request)
    {
        $errors = [];
    
        // Validación manual
        if (!$request->filled('name') || !preg_match('/^[A-Za-zÁ-ÿ\s]+$/', $request->name)) {
            $errors[] = 'El nombre es obligatorio y solo debe contener letras.';
        }
    
        if (!$request->filled('email') || !filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        } elseif (!preg_match('/^([a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.com))$/', $request->email)) {
            $errors[] = 'Solo se permiten correos de Gmail o Hotmail.';
        } elseif (User::where('email', $request->email)->exists()) {
            $errors[] = 'El correo ya está registrado.';
        }
    
        if (!$request->filled('password')) {
            $errors[] = 'La contraseña es obligatoria.';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/', $request->password)) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres, incluyendo una mayúscula, una minúscula, un número y un símbolo.';
        }
    
        if ($request->password !== $request->password_confirmation) {
            $errors[] = 'La confirmación de contraseña no coincide.';
        }
    
        if (!$request->filled('g-recaptcha-response')) {
            $errors[] = 'Debe resolver el reCAPTCHA.';
        } elseif (!NoCaptcha::check($request->input('g-recaptcha-response'))) {
            $errors[] = 'El reCAPTCHA no fue válido.';
        }
    
        // Si hay errores, redireccionar con mensajes personalizados
        if (!empty($errors)) {
            return redirect()->route('register.show')
                ->with('register_errors', $errors)
                ->withInput($request->except('password', 'password_confirmation'));
        }
    
        try {
            // Crear usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ]);
    
            // Generar link firmado
            $url = URL::temporarySignedRoute(
                'verification.email',
                now()->addHours(24),
                ['id' => $user->id]
            );
    
            // Enviar correo
            Mail::to($user->email)->send(new EmailVerification($url, $user->name));
    
            return redirect()->route('login')->with('success', 
                '¡Registro exitoso! Te enviamos un correo a ' . $user->email . 
                ' con un enlace para verificar tu cuenta. Por favor revisa tu bandeja de entrada o spam. El enlace expira en 24 horas.'
            );
        } catch (\Exception $e) {
            return redirect()->route('register.show')
                ->with('register_errors', ['Hubo un problema al enviar el correo de verificación. Intenta de nuevo.'])
                ->withInput($request->except('password', 'password_confirmation'));
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
