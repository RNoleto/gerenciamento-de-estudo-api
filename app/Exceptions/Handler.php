<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Tratamento para erros 404
        if($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException){
            return response()->view('errors.404', [
                'errorMessage' => 'A página que você está procurando não foi encontrada.',
                'requestedUrl' => $request->url(),
            ], 404);
        }

        // Tratamento para erros 500 (erros de servidor interno)
        if($this->isHttpException($exception) && $exception->getStatusCode() === 500){
            return response()->view('errors.500', [
                'errorMessage' => 'Ocorreu um erro interno no servidor. Por favor, tente novamente mais tarde.',
            ], 500);
        }

        // Fallback para outros erros
        return parent::render($request, $exception);
    }
}
