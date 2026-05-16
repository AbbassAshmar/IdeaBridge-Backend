<?php

namespace App\Modules\Users\Services;

use App\Exceptions\UserRepositoryError;
use App\Exceptions\UsersDomainError;
use App\Modules\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class UsersService
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getAuthenticatedUser(int $authenticatedUserId): array
    {
        try {
            $user = $this->userRepository->findUserById($authenticatedUserId);

            if (! $user) {
                throw new UsersDomainError('The authenticated user was not found.', status: 404);
            }

            return [
                'user' => $user,
            ];
        } catch (UsersDomainError $throwable) {
            throw $throwable;
        } catch (UserRepositoryError $throwable) {
            Log::error('Unable to load authenticated user due to repository error.', [
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new UsersDomainError('Unable to load user profile.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to load authenticated user due to unexpected error.', [
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new UsersDomainError('Unable to load user profile.'))->causeBy($throwable);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateProfile(int $authenticatedUserId, array $payload): array
    {
        try {
            $updatedUser = $this->userRepository->updateUserById($authenticatedUserId, [
                'username' => (string) $payload['username'],
                'email' => (string) $payload['email'],
            ]);

            Log::info('User profile updated successfully.', [
                'user_id' => $authenticatedUserId,
                'username' => (string) $payload['username'],
                'email' => (string) $payload['email'],
            ]);

            return [
                'message' => 'Profile updated successfully.',
                'user' => $updatedUser,
            ];
        } catch (UserRepositoryError $throwable) {
            Log::error('Unable to update user profile due to repository error.', [
                'user_id' => $authenticatedUserId,
                'username' => (string) ($payload['username'] ?? ''),
                'email' => (string) ($payload['email'] ?? ''),
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new UsersDomainError('Unable to update user profile.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to update user profile due to unexpected error.', [
                'user_id' => $authenticatedUserId,
                'username' => (string) ($payload['username'] ?? ''),
                'email' => (string) ($payload['email'] ?? ''),
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new UsersDomainError('Unable to update user profile.'))->causeBy($throwable);
        }
    }
}
