<?php

namespace App\Services;

use App\Enums\TransactionTypeEnum;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Objects\MoneyObject;
use Exception;
use Illuminate\Support\Facades\DB;

class WalletService
{
    protected Wallet $wallet;

    private function __construct(Wallet $wallet)
    {
        $this->wallet = $wallet;
    }

    public static function init(?string $ownerName = null, ?string $currencyCode = null, ?int $walletId = null): static
    {
        $wallet = null;
        if (!empty($ownerName) && !empty($currencyCode)) {
            $currency = Currency::where('code', $currencyCode)->firstOrFail();
            $wallet = Wallet::query()->firstOrCreate([
                'owner_name' => $ownerName,
                'currency_id' => $currency->id,
            ], [
                'balance' => 0
            ]);
        } elseif ($walletId) {
            $wallet = Wallet::query()->findOrFail($walletId);
        }

        abort_if($wallet === null, 404, __('messages.not_found'));
        return new static($wallet);
    }

    public function deposit(int $amount): array
    {
        return DB::transaction(function () use ($amount) {
            $this->wallet->lock();
            $this->wallet->balance = $this->wallet->balance->increase($amount);
            $this->wallet->save();

            $transaction = Transaction::create([
                'wallet_id' => $this->wallet->id,
                'currency_id' => $this->wallet->currency_id,
                'type' => TransactionTypeEnum::CREDIT,
                'amount' => $amount,
            ]);
            $this->wallet->lock(false);

            return [
                'wallet_id' => $this->wallet->id,
                'transaction_id' => $transaction->id,
                'balance' => $this->wallet->balance->format(),
            ];
        });
    }

    public function withdraw(int $amount): array
    {
        return DB::transaction(function () use ($amount) {

            abort_if($this->wallet->balance->isLessThan(new MoneyObject($amount, $this->wallet->currency->decimal_places)), 400, __('messages.insufficient_funds'));
            $this->wallet->lock();
            $this->wallet->balance = $this->wallet->balance->decrease($amount);
            $this->wallet->save();

            $transaction = Transaction::create([
                'wallet_id' => $this->wallet->id,
                'currency_id' => $this->wallet->currency_id,
                'type' => TransactionTypeEnum::DEBIT,
                'amount' => $amount,
            ]);
            $this->wallet->lock(false);

            return [
                'wallet_id' => $this->wallet->id,
                'transaction_id' => $transaction->id,
                'balance' => $this->wallet->balance->format(),
            ];
        });
    }

    public function transferTo(Wallet|int $targetWallet, int $amount): array
    {
        $target = $targetWallet instanceof Wallet ? $targetWallet : Wallet::query()->findOrFail($targetWallet);
        return DB::transaction(function () use ($target, $amount) {
            $this->wallet->lock();
            $target->lock();
            $source = $this->wallet;

            if ($source->id === $target->id) {
                throw new Exception('Cannot transfer to the same wallet');
            }

            if ($source->currency_id !== $target->currency_id) {
                throw new Exception('Currency mismatch');
            }

            if ($source->balance->isLessThan(new MoneyObject($amount, $target->currency->decimal_places))) {
                throw new Exception('Insufficient funds');
            }

            $source->balance = $source->balance->decrease($amount);
            $target->balance = $target->balance->increase($amount);

            $source->save();
            $target->save();

            $debitTransaction = Transaction::create([
                'wallet_id' => $source->id,
                'type' => TransactionTypeEnum::DEBIT,
                'amount' => $amount,
                'currency_id' => $target->currency_id,
                'related_wallet_id' => $target->id,
            ]);

            $creditTransaction = Transaction::create([
                'wallet_id' => $target->id,
                'type' => TransactionTypeEnum::CREDIT,
                'amount' => $amount,
                'currency_id' => $source->currency_id,
                'related_wallet_id' => $source->id,
            ]);

            return [
                'source_wallet_id' => $source->id,
                'source_balance' => $source->balance->format(),
                'target_wallet_id' => $target->id,
                'target_balance' => $target->balance->format(),
                'debit_transaction_id' => $debitTransaction->id,
                'credit_transaction_id' => $creditTransaction->id,
            ];
        });
    }

    public function getBalance(): array
    {
        return ['balance' => $this->wallet->balance->format()];
    }
}
