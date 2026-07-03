<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserRepository
{
    /**
     * Get all users with cache
     */
    public function getAllUsers($page = 1, $perPage = 15)
    {
        $cacheKey = "users_page_{$page}_{$perPage}";

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($perPage) {
            return User::paginate($perPage);
        });
    }

    /**
     * Get user by ID with cache
     */
    public function getUserById($id)
    {
        return Cache::remember("user_{$id}", now()->addHours(24), function () use ($id) {
            return User::with('profile', 'files')->find($id);
        });
    }

    /**
     * Create user
     */
    public function createUser(array $data)
    {
        $user = User::create($data);
        Cache::forget('users_page_*');
        return $user;
    }

    /**
     * Update user
     */
    public function updateUser($id, array $data)
    {
        $user = User::find($id);
        if ($user) {
            $user->update($data);
            Cache::forget("user_{$id}");
            Cache::forget('users_page_*');
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
            $user->delete();
            Cache::forget("user_{$id}");
            Cache::forget('users_page_*');
        }
        return $user;
    }

    /**
     * Search users
     */
    public function searchUsers($search)
    {
        return User::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->get();
    }
}
