<?php

namespace App\Modules\Users\Repositories;

use App\Exceptions\UserRepositoryError;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findUserByEmail(string $email): ?array
    {
        try {
            $user = User::query()->where('email', $email)->first();

            return $user ? $this->mapUser($user, true) : null;
        } catch (Throwable $throwable) {
            Log::error('Failed to find user by email.', [
                'email' => $email,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new UserRepositoryError('Unable to load user by email.'))->causeBy($throwable);
        }
    }

    public function createUser(array $userData): array
    {
        try {
            $user = User::query()->create([
                'username' => (string) $userData['username'],
                'email' => (string) $userData['email'],
                'password' => (string) $userData['password'],
            ]);

            return $this->mapUser($user);
        } catch (Throwable $throwable) {
            Log::error('Failed to create user.', [
                'username' => (string) ($userData['username'] ?? ''),
                'email' => (string) ($userData['email'] ?? ''),
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new UserRepositoryError('Unable to create user.'))->causeBy($throwable);
        }
    }

    public function findUserById(int $userId): ?array
    {
        try {
            $user = User::query()->find($userId);

            return $user ? $this->mapUser($user) : null;
        } catch (Throwable $throwable) {
            Log::error('Failed to find user by ID.', [
                'user_id' => $userId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new UserRepositoryError('Unable to load user by ID.'))->causeBy($throwable);
        }
    }

    public function updateUserById(int $userId, array $userData): array
    {
        try {
            $user = User::query()->findOrFail($userId);
            $user->fill([
                'username' => (string) $userData['username'],
                'email' => (string) $userData['email'],
            ]);
            $user->save();
            $user->refresh();

            return $this->mapUser($user);
        } catch (Throwable $throwable) {
            Log::error('Failed to update user profile.', [
                'user_id' => $userId,
                'username' => (string) ($userData['username'] ?? ''),
                'email' => (string) ($userData['email'] ?? ''),
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new UserRepositoryError('Unable to update user profile.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapUser(User $user, bool $withPasswordHash = false): array
    {
        $payload = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->values()->all(),
            'permissions' => $user->getPermissionNames()->values()->all(),
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
        ];

        if ($withPasswordHash) {
            $payload['password_hash'] = $user->password;
        }

        return $payload;
    }
}
