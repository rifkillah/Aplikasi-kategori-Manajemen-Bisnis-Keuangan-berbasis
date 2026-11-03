<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $transaction->tenant_id;
    }

    public function void(User $user, Transaction $transaction): bool
    {
        return $this->view($user, $transaction) && $user->hasRole('owner');
    }
}
