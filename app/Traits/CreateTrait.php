<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait CreateTrait
{
    public function storeItem(Request $request): Model
    {
        $data = !is_null($this->storeRequest) ? app($this->storeRequest)->validated() : $this->storeValidate($request);
        $data = array_merge($data, $this->storeAdditionalData($data));
        $created = $this->createModel($data);
        $this->afterStore($created, $request);
        return $created;
    }

    protected function createModel(array $data)
    {
        return $this->model::query()->create($data);
    }

    public function storeAdditionalData(array $data): array
    {
        return [];
    }

    public function afterStore(Model $model, Request $request): void
    {
        $this->afterSave($model, $request);
    }

    public function storeRules(): array
    {
        return $this->rules();
    }

    public function storeValidate(Request $request): array
    {
        return $request->validate($this->storeRules());
    }
}
