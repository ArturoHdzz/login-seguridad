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
     * @return \Illuminate\Http\RedirectResponse
     */
}