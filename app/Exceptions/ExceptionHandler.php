<?php

namespace App\Exceptions;

use App\Traits\ResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class ExceptionHandler
{
    use ResponseTrait;

    public function __construct()
    {
    }

    public function handleException($request, Throwable $exception): Response|JsonResponse|RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {

        if ($exception instanceof AuthenticationException) {
            return $this->notAuthorizedResponse($exception->getMessage());
        }

        if ($exception instanceof AuthorizationException) {
            return $this->notAuthorizedResponse($exception->getMessage());
        }

        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return $this->notFoundResponse();
        }

        if ($exception instanceof HttpException) {
            if ($exception->getMessage() == 'Unauthorized Action') {
                return $this->forbiddenResponse($exception->getMessage());
            }

            return $this->badRequestResponse($exception->getMessage());
        }

        if ($exception instanceof HttpResponseException) {
            return $this->badRequestResponse($exception->getMessage());
        }

        if ($exception instanceof ValidationException) {
            return $this->validationErrorResponse($exception->errors());
        }

        if ($exception instanceof RouteNotFoundException) {
            if ($exception->getMessage() == 'Route [login] not defined.') {
                return $this->notAuthorizedResponse($exception->getMessage());
            }
        }

        if (!app()->environment('local')) {
            logger()->error($exception->getMessage());
            logger()->error($exception->getTraceAsString());
            return $this->notFoundResponse();
        }

        return $this->serverErrorResponse([
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTrace(),
        ]);
    }
}
