<?php

namespace App\Modules\Users\Repositories;

use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findUserByEmail(string $email): ?array
    {
        $user = User::query()->where('email', $email)->first();

        return $user ? $this->mapUser($user, true) : null;
    }

    public function createUser(array $userData): array
    {
        $user = User::query()->create([
            'username' => (string) $userData['username'],
            'email' => (string) $userData['email'],
            'password' => (string) $userData['password'],
        ]);

        return $this->mapUser($user);
    }

    public function findUserById(int $userId): ?array
    {
        $user = User::query()->find($userId);

        return $user ? $this->mapUser($user) : null;
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
