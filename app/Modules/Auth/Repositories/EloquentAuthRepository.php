<?php

namespace App\Modules\Auth\Repositories;

use App\Exceptions\AuthRepositoryError;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class EloquentAuthRepository implements AuthRepositoryInterface
{
    public function assignRole(int $userId, string $roleName): void
    {
        try {
            $user = User::query()->findOrFail($userId);

            $user->assignRole($roleName);
        } catch (Throwable $throwable) {
            Log::error('Failed to assign role to user.', [
                'user_id' => $userId,
                'role' => $roleName,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new AuthRepositoryError('Unable to assign role to user.'))->causeBy($throwable);
        }
    }
}
