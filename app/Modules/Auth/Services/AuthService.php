<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Repositories\AuthRepositoryInterface;
use App\Modules\Users\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly AuthRepositoryInterface $authRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function register(Request $request, array $payload): array
    {
        $newUser = $this->userRepository->createUser([
            'username' => $payload['username'],
            'email' => $payload['email'],
            'password' => Hash::make((string) $payload['password']),
        ]);

        $this->authRepository->assignRole($newUser['id'], $this->resolveRoleName((string) $payload['role']));

        Auth::guard('web')->loginUsingId($newUser['id']);
        $request->session()->regenerate();

        return [
            'message' => 'Registered successfully.',
            'user' => $this->userRepository->findUserById($newUser['id']),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function login(Request $request, array $payload): array
    {
        $user = $this->userRepository->findUserByEmail((string) $payload['email']);

        if (! $user || ! Hash::check((string) $payload['password'], (string) ($user['password_hash'] ?? ''))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Auth::guard('web')->loginUsingId((int) $user['id']);
        $request->session()->regenerate();

        return [
            'message' => 'Logged in successfully.',
            'user' => $this->userRepository->findUserById((int) $user['id']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function logout(Request $request): array
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return [
            'message' => 'Logged out successfully.',
        ];
    }

    private function resolveRoleName(string $role): string
    {
        return match (strtolower($role)) {
            'developer' => 'Developer',
            default => 'User',
        };
    }
}
