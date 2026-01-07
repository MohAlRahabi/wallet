<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_wallet_id' => ['required', 'integer', 'exists:wallets,id'],
            'amount' => ['required', 'integer', 'min:0'],
        ];
    }
}
