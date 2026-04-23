<?php

namespace App\Modules\Auth\Services;

use App\Exceptions\AuthDomainError;
use App\Exceptions\AuthRepositoryError;
use App\Exceptions\UserRepositoryError;
use App\Modules\Auth\Repositories\AuthRepositoryInterface;
use App\Modules\Users\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

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
        try {
            $newUser = $this->userRepository->createUser([
                'username' => $payload['username'],
                'email' => $payload['email'],
                'password' => Hash::make((string) $payload['password']),
            ]);

            log::info('New user created successfully.', [
                'user_id' => (int) $newUser['id'],
                'email' => (string) ($newUser['email'] ?? ''),
            ]);

            $this->authRepository->assignRole($newUser['id'], $this->resolveRoleName((string) $payload['role']));

            Auth::guard('web')->loginUsingId($newUser['id']);
            $request->session()->regenerate();

            Log::info('User registered successfully.', [
                'user_id' => (int) $newUser['id'],
                'email' => (string) ($newUser['email'] ?? ''),
            ]);

            return [
                'message' => 'Registered successfully.',
                'user' => $this->userRepository->findUserById($newUser['id']),
            ];
        } catch (UserRepositoryError|AuthRepositoryError $throwable) {
            Log::error('Registration failed due to repository error.', [
                'email' => (string) ($payload['email'] ?? ''),
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new AuthDomainError('Unable to register user.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Registration failed unexpectedly.', [
                'email' => (string) ($payload['email'] ?? ''),
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new AuthDomainError('Unable to complete registration.'))->causeBy($throwable);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function login(Request $request, array $payload): array
    {
        try {
            $user = $this->userRepository->findUserByEmail((string) $payload['email']);

            if (! $user || ! Hash::check((string) $payload['password'], (string) ($user['password_hash'] ?? ''))) {
                Log::warning('Login failed due to invalid credentials.', [
                    'email' => (string) $payload['email'],
                ]);

                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            Auth::guard('web')->loginUsingId((int) $user['id']);
            $request->session()->regenerate();

            Log::info('User logged in successfully.', [
                'user_id' => (int) $user['id'],
                'email' => (string) $payload['email'],
            ]);

            return [
                'message' => 'Logged in successfully.',
                'user' => $this->userRepository->findUserById((int) $user['id']),
            ];
        } catch (ValidationException $throwable) {
            throw $throwable;
        } catch (UserRepositoryError $throwable) {
            Log::error('Login failed due to repository error.', [
                'email' => (string) ($payload['email'] ?? ''),
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new AuthDomainError('Unable to login user.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Login failed unexpectedly.', [
                'email' => (string) ($payload['email'] ?? ''),
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new AuthDomainError('Unable to complete login.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function logout(Request $request): array
    {
        try {
            $userId = (int) ($request->user()?->id ?? 0);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Log::info('User logged out successfully.', [
                'user_id' => $userId,
            ]);

            return [
                'message' => 'Logged out successfully.',
            ];
        } catch (Throwable $throwable) {
            Log::error('Logout failed unexpectedly.', [
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new AuthDomainError('Unable to complete logout.'))->causeBy($throwable);
        }
    }

    private function resolveRoleName(string $role): string
    {
        return match (strtolower($role)) {
            'developer' => 'Developer',
            default => 'User',
        };
    }
}
