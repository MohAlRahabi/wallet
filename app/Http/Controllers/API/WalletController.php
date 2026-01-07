<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\DepositWithdrawRequest;
use App\Http\Requests\WalletRequest;
use App\Http\Requests\WalletTransferRequest;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use App\Services\WalletService;

class WalletController extends BaseApiController
{
    protected string $model = Wallet::class;
    protected ?string $storeRequest = WalletRequest::class;
    protected ?string $updateRequest = WalletRequest::class;
    protected ?string $resource = WalletResource::class;

    public function getRelations(): array
    {
        return ['currency'];
    }

    public function filterFields(): array
    {
        return [
            ['name' => 'currency_id'],
            ['name' => 'owner_name'],
        ];
    }

    public function deposit(int $walletId, DepositWithdrawRequest $request)
    {
        $amount = $request->validated('amount');
        $data = WalletService::init(walletId: $walletId)->deposit($amount);

        return $this->successResponse($data);
    }

    public function withdraw(int $walletId, DepositWithdrawRequest $request)
    {
        $amount = $request->validated('amount');
        $data = WalletService::init(walletId: $walletId)->withdraw($amount);

        return $this->successResponse($data);
    }

    public function transfer(int $walletId, WalletTransferRequest $request)
    {
        $data = $request->validated();
        $amount = $data['amount'];
        $toWalletId = $data['to_wallet_id'];
        $data = WalletService::init(walletId: $walletId)->transferTo($toWalletId, $amount);

        return $this->successResponse($data);
    }

    public function balance(int $walletId)
    {
        $data = WalletService::init(walletId: $walletId)->getBalance();

        return $this->successResponse($data);
    }
}
