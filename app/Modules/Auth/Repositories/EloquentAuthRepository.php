<?php

namespace App\Modules\Auth\Repositories;

use App\Models\User;

class EloquentAuthRepository implements AuthRepositoryInterface
{
    public function assignRole(int $userId, string $roleName): void
    {
        $user = User::query()->findOrFail($userId);

        $user->assignRole($roleName);
    }
}
