<?php

namespace App\Http\Controllers;

use App\Traits\CreateTrait;
use App\Traits\DestroyTrait;
use App\Traits\IndexTrait;
use App\Traits\ResponseTrait;
use App\Traits\ShowTrait;
use App\Traits\UpdateTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class Controller
{
    use IndexTrait,
        ShowTrait,
        CreateTrait,
        UpdateTrait,
        DestroyTrait,
        ResponseTrait;

    protected string $model = Model::class;
    protected ?string $resource = null;
    protected ?string $storeRequest = null;
    protected ?string $updateRequest = null;

    public function __construct()
    {
        $this->addToConstruct();
    }

    protected function addToConstruct(): void
    {

    }

    public function index(): JsonResponse
    {
        if ($this->paginated) {
            $data = $this->getData()->toArray();
            return $this->successResponse($data['data'], meta: $data['meta']);
        }
        return $this->successResponse($this->getData());
    }

    public function show(string|int $id): JsonResponse
    {
        $item = $this->findItem($id);
        if ($item) {
            return $this->successResponse($this->formatItem($item));
        }
        return $this->notFoundResponse();
    }

    public function store(Request $request): JsonResponse
    {
        return $this->successResponse($this->formatItem($this->storeItem($request)), 201);
    }

    public function update(string|int $id, Request $request): JsonResponse
    {
        $item = $this->updateItem($id, $request);
        if ($item) {
            return $this->response($this->formatItem($item));
        }
        return $this->notFoundResponse();
    }

    public function destroy(string|int $id): JsonResponse
    {
        $item = $this->deleteItem($id);
        if ($item) {
            return $this->successResponse($item);
        }
        return $this->notFoundResponse();
    }

    public function rules(?Model $model = null): array
    {
        return [];
    }

    public function afterSave(Model $model, Request $request): void
    {

    }

    public function search(): JsonResponse
    {
        if ($this->paginated) {
            $this->isSearch = true;
            $data = $this->getData()->toArray();
            return $this->successResponse($data['data'], meta: $data['meta']);
        }
        return $this->successResponse($this->getData());
    }
}
