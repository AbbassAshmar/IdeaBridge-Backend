<?php

namespace App\Modules\Users\Repositories;

interface UserRepositoryInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function findUserByEmail(string $email): ?array;

    /**
     * @param  array<string, mixed>  $userData
     * @return array<string, mixed>
     */
    public function createUser(array $userData): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findUserById(int $userId): ?array;

    /**
     * @param  array<string, mixed>  $userData
     * @return array<string, mixed>
     */
    public function updateUserById(int $userId, array $userData): array;
}
