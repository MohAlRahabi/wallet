<?php

namespace App\Models;

use App\Casts\Money;
use App\Objects\MoneyObject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int id
 * @property int currency_id
 * @property MoneyObject balance
 * @property Currency currency
 * @mixin Builder
 */
class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_name',
        'currency_id',
        'balance',
    ];

    protected $casts = [
        'currency_id' => 'integer',
        'balance' => Money::class,
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
