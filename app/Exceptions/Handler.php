<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
// use Symfony\Component\HttpKernel\Exception\ModelNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {

        if ($request->expectsJson()) {

            $response = [
                'status_code' => '401',
                'status_message' => 'fail',
                'error' => 'Unauthenticated'
            ];

            return response()->json($response, 401, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
    }
    public function render($request, Exception $exception)
    {
        if ($request->wantsJson()) {


            if ($exception instanceof NotFoundHttpException) {
                $response = [
                'status_code' => '404',
                'status_message' => 'fail',
                'error' => 'Route not found!',
            ];
                return response()->json($response, 404, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }


            if ($exception instanceof ModelNotFoundException) {
                $response = [
                    'status_code' => '400',
                    'status_message' => 'fail',
                    'error' => 'No query results for model!',
                ];
                return response()->json($response, 400, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }
            
            if ($exception instanceof MethodNotAllowedHttpException) {
                $response = [
                'status_code' => '400',
                'status_message' => 'fail',
                'error' => 'Method (POST/GET) is not supported for this route!',
            ];
                return response()->json($response, 400, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }
        }
        return parent::render($request, $exception);
    }
}
