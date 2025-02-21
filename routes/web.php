<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

// Ensure that the RegistroController class exists in the specified namespace
// If it does not exist, create the class in the App\Http\Controllers namespace

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


/**
 * Main routes group
 * Middlewares:
 * - web: Session, CSRF, and cookies
 * - prevent-back: Prevent back navigation for security reasons
 */
Route::middleware(['web', 'prevent-back'])->group(function () {
    /**
     * Root route - Redirects to login
     */
    Route::get('/', function () {
        return redirect()->route('login');
    });

    /**
     * Public routes - Accessible only by unauthenticated users
     * Middleware 'guest' ensures authenticated users cannot access these routes
     */
    Route::middleware('guest')->group(function () {
        /**
         * User Registration Routes
         */
        Route::controller(RegisterController::class)->group(function () {
            // Registration form
            Route::get('/register', 'showRegistrationForm')
                ->name('register.show');

            // Process registration
            Route::post('/register', 'register')
                ->name('register')
                ->where('email', '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$'); // Basic email validation

            // Post-registration verification view
            Route::get('/verification/registration', function () {
                return view('auth.registration-verification');
            })
            ->name('verification.registration')
            ->middleware('verification.state');

            // Verify registration code
            Route::post('/verification/registration', 'verifyRegistration')
                ->name('verification.registration.verify');
        });

        /**
         * Authentication (Login) Routes
         * Implements the two-step login process
         */
        Route::controller(LoginController::class)->group(function () {
            // Login form
            Route::get('/login', 'showLoginForm')
                ->name('login');
            
            // First step of authentication
            Route::post('/login/step1', 'step1')
                ->name('login.step1');
            
            // Verification form
            Route::get('/login/verification', 'showVerificationForm')
                ->name('login.verification');
            
            // Verify authentication code
            Route::post('/login/verify', 'verifyCode')
                ->name('login.verify');
        });
    });

    /**
     * Email verification route
     * Signed URL for account activation
     */
    Route::get('/email/verify/{id}', [RegisterController::class, 'verifyEmail'])
        ->name('verification.email')
        ->middleware('signed')
        ->where('id', '[0-9]+'); // Ensures that the ID is numeric

    /**
     * Protected area - Accessible only by authenticated users
     * Middlewares:
     * - auth: Ensures the user is authenticated
     * - check.user.session: Validates the user's session
     */
    Route::middleware(['auth', 'check.user.session', 'verified.email'])->group(function () {
        // Dashboard
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        // Logout
        Route::post('/logout', [LoginController::class, 'logout'])
            ->name('logout');
    });
});