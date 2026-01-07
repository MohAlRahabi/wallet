<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\TransactionTypeEnum;
use App\Objects\MoneyObject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int id
 * @property int wallet_id
 * @property TransactionTypeEnum type
 * @property MoneyObject amount
 * @property int related_wallet_id
 * @property int currency_id
 * @mixin Builder
 */
class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'related_wallet_id',
        'currency_id',
    ];

    protected $casts = [
        'type' => TransactionTypeEnum::class,
        'amount' => Money::class,
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function relatedWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'related_wallet_id');
    }
}
