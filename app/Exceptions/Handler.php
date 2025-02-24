<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Session\TokenMismatchException;

class Handler extends ExceptionHandler
{
    /**
     * List of exceptions that will not be reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [];

    /**
     * List of input fields that will never be flashed to the session on validation errors.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e) {
            // Exclude validation errors
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return $this->handleValidationException($e);
            }

            // Handle 404 errors
            if ($e instanceof NotFoundHttpException) {
                return $this->handleHttpException($e);
            }

            // Handle 419 CSRF token mismatch errors
            if ($e instanceof TokenMismatchException) {
                return $this->handleTokenMismatchException($e);
            }
        });
    }

    /**
     * Handle validation errors.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function handleValidationException($e)
    {
        if (request()->expectsJson()) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        return redirect()->back()->withInput()->withErrors($e->errors());
    }

    /**
     * Handle HTTP exceptions.
     *
     * @param  \Throwable  $e
     * @return \Illuminate\Http\Response
     */
    protected function handleHttpException($e)
    {
        if ($e instanceof NotFoundHttpException) {
            return response()->view('errors.404', [], 404);
        }
        
        return parent::render(request(), $e);
    }

    /**
     * Handle token mismatch exceptions (419 errors).
     *
     * @param  \Illuminate\Session\TokenMismatchException  $e
     * @return \Illuminate\Http\Response
     */
    protected function handleTokenMismatchException($e)
    {
        return response()->view('errors.419', [
            'message' => 'Your session has expired. Please try again.'
        ], 419);
    }

    /**
     * Handle HTTP exceptions.
     *
     * @param  \Throwable  $e
     * @return \Illuminate\Http\RedirectResponse
     */
}