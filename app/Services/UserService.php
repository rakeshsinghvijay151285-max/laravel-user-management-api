<?php

namespace App\Services;

use App\Events\UserCreated;
use App\Events\UserDeleted;
use App\Events\UserUpdated;
use App\Jobs\ProcessUserData;
use App\Jobs\PublishUserEventToKafka;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users
     */
    public function getAllUsers($page = 1, $perPage = 15)
    {
        return $this->userRepository->getAllUsers($page, $perPage);
    }

    /**
     * Get user by ID
     */
    public function getUserById($id)
    {
        return $this->userRepository->getUserById($id);
    }

    /**
     * Create new user
     */
    public function createUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);

        $user = $this->userRepository->createUser($data);

        // Dispatch events
        event(new UserCreated($user));
        ProcessUserData::dispatch($user);
        PublishUserEventToKafka::dispatch($user->id, 'user_created', $data);

        return $user;
    }

    /**
     * Update user
     */
    public function updateUser($id, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user = $this->userRepository->updateUser($id, $data);

        if ($user) {
            event(new UserUpdated($user));
            PublishUserEventToKafka::dispatch($user->id, 'user_updated', $data);
        }

        return $user;
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        $user = User::find($id);

        if ($user) {
            $this->userRepository->deleteUser($id);
            event(new UserDeleted($id));
            PublishUserEventToKafka::dispatch($id, 'user_deleted', []);
        }

        return $user;
    }

    /**
     * Search users
     */
    public function searchUsers($search)
    {
        return $this->userRepository->searchUsers($search);
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar($user, $file)
    {
        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $file->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return $path;
    }
}
