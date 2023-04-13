<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace App\Exceptions;

use HighKer\Core\Enum\ResponseCode;
use HighKer\Core\Exceptions\HighKerException;
use HighKer\Core\Traits\ApiResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        HighKerException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
    public function register()
    {
        $this->reportable(function (Throwable $e) {
        });
    }

    public function render($request, Throwable $e)
    {
        if (Str::startsWith(request()->getHost(), 'api')) {
            if ($e instanceof ThrottleRequestsException) {
                return $this->fail('请求次数过多');
            }
            if ($e instanceof ModelNotFoundException) {
                return $this->fail('该模型未找到');
            }
            if ($e instanceof NotFoundHttpException) {
                return $this->fail('没有找到该页面');
            }
            if ($e instanceof ValidationException) {
                return $this->fail($e->validator->errors()->first());
            }
            if ($e instanceof AuthenticationException) {
                return $this->fail(ResponseCode::MAP[ResponseCode::UNAUTHORIZED], ResponseCode::UNAUTHORIZED);
            }
            if ($e instanceof HttpException) {
                return $this->fail($e->getMessage());
            }
            if ($e instanceof AuthorizationException) {
                $message = $e->getMessage() ? $e->getMessage() : '没有此权限';

                return $this->fail($message);
            }
            if ($e instanceof HighKerException) {
                return $this->fail($e->getMessage(), $e->getCode());
            }
        }

        return parent::render($request, $e);
    }
}
