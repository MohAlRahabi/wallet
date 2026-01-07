<?php

namespace App\Traits;


use Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    public function response(mixed $data = [], int $statusCode = 200, string $message = "success", array $meta = []): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'status' => $statusCode == 200 || $statusCode == 201 || $statusCode == 204 || $statusCode == 205,
            'message' => $message,
            'code' => $statusCode,
            'meta' => $meta,
        ], $statusCode);
    }

    public function successResponse(mixed $data = [], int $statusCode = 200, ?string $message = null, array $meta = []): JsonResponse
    {
        return $this->response($data, $statusCode, $message ?? $this->successMessage(), $meta);
    }

    public function errorResponse(mixed $data = [], int $statusCode = 400, ?string $message = null): JsonResponse
    {
        return $this->response($data, $statusCode, $message ?? $this->errorMessage());
    }

    public function notFoundResponse(?string $message = null): JsonResponse
    {
        return $this->errorResponse(null, 404, $message ?? $this->notFoundResponseMessage());
    }

    public function serverErrorResponse(mixed $data = [], ?string $message = null): JsonResponse
    {
        return $this->errorResponse($data, 500, $message ?? $this->serverErrorMessage());
    }

    public function notAuthorizedResponse(?string $message = null): JsonResponse
    {
        return $this->errorResponse(null, 401, $message ?? $this->notAuthorizedMessage());
    }

    public function forbiddenResponse(?string $message = null): JsonResponse
    {
        return $this->errorResponse(null, 403, $message ?? $this->forbiddenMessage());
    }

    public function badRequestResponse(?string $message = null): JsonResponse
    {
        return $this->errorResponse(null, 400, $message ?? $this->badRequestMessage());
    }

    public function validationErrorResponse(mixed $data = [], ?string $message = null): JsonResponse
    {
        return $this->errorResponse($data, 422, $message ?? $this->validationErrorMessage());
    }

    public function validationErrorMessage(): string
    {
        return __('messages.validation_error');
    }

    public function serverErrorMessage(): string
    {
        return __('messages.server_error');
    }

    public function forbiddenMessage(): string
    {
        return __('messages.forbidden');
    }

    public function notAuthorizedMessage(): string
    {
        return __('messages.not_authorized');
    }

    public function badRequestMessage(): string
    {
        return __('messages.bad_request');
    }

    public function successMessage(): string
    {
        return __('messages.success');
    }


    public function errorMessage(): string
    {
        return __('messages.error');
    }


    public function notFoundResponseMessage(): string
    {
        return __('messages.not_found');
    }
}
