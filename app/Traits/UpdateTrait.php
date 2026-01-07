<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait UpdateTrait
{
    public function updateItem(Model|int $id, Request $request): ?Model
    {
        $item = $this->findItem($id);
        $data = !is_null($this->updateRequest) ? app($this->updateRequest)->validated() : $this->updateValidate($item, $request);
        $data = array_merge($data, $this->updateAdditionalData($data));
        if (is_null($item)) return null;
        $this->updateModel($item, $data);
        $this->afterUpdate($item, $request);
        return $item;
    }

    public function updateModel(Model $item, array $data): void
    {
        $item->update($data);
    }

    public function updateAdditionalData(array $data): array
    {
        return [];
    }

    public function afterUpdate(Model $model, Request $request): void
    {
        $this->afterSave($model, $request);
    }

    public function updateRules(?Model $model = null): array
    {
        return $this->rules($model);
    }

    public function updateValidate(?Model $model, Request $request): array
    {
        return $request->validate($this->updateRules($model));
    }
}
