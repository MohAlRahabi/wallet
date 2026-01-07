<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait DestroyTrait
{
    public function deleteItem(Model|int $id): bool
    {
        $item = $this->findItem($id);
        if (is_null($item)) return false;
        $this->beforeDelete($item);
        $result = $item->delete();
        if ($result) {
            $this->afterDelete();
        }
        return $result;
    }

    public function afterDelete(): void
    {

    }

    public function beforeDelete(Model $item): void
    {

    }
}
