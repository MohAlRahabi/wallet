<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_name' => ['required', 'string', 'min:3', 'max:255', 'unique:wallets,owner_name'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
        ];
    }
}
