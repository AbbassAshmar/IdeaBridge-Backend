<?php

namespace App\Modules\Auth\Repositories;

interface AuthRepositoryInterface
{
    public function assignRole(int $userId, string $roleName): void;
}
