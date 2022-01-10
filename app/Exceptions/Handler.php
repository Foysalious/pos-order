<?php namespace App\Exceptions;

use App\Traits\ResponseAPI;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ResponseAPI;
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
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        $this->renderable(function (Throwable $e) {
            return $this->handleException($e);
        });
        $this->reportable(function (Throwable $e) {
            if ($this->shouldReport($e) && app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }

    public function handleException(Throwable $e)
    {
        if ($e instanceof HttpException) {
            $code = $e->getStatusCode();
            $defaultMessage = Response::$statusTexts[$code];
            $message = $e->getMessage() == "" ? $defaultMessage : $e->getMessage();
            return $this->error($message, $code);
        } elseif ($e instanceof ModelNotFoundException) {
            $model = strtolower(class_basename($e->getModel()));
            return $this->error("Does not exist any instance of {$model} with the given id", Response::HTTP_NOT_FOUND);
        } elseif ($e instanceof AuthorizationException) {
            return $this->error($e->getMessage(), Response::HTTP_FORBIDDEN);
        } elseif ($e instanceof AuthenticationException) {
            return $this->error($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        } elseif ($e instanceof ValidationException) {
            $errors = $e->validator->errors()->all();
            return $this->error(getValidationErrorMessage($errors), Response::HTTP_UNPROCESSABLE_ENTITY);
        } elseif ($e instanceof BaseException || $e instanceof DoNotReportException) {
            return $this->error($e->getMessage(), $e->getCode());
        } else {
            $response = [];
            $response['message'] = 'Something went wrong';
            if ($this->wantsTrace()) {
                $response['exception'] = [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }
            return response($response,(int) $e->getCode() ?: 500 );
        }
    }

    private function wantsTrace(): bool
    {
        return config('app.env') != 'production';
    }
}
