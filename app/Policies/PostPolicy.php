<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManagePosts($user);
    }

    public function view(User $user, Post $post): bool
    {
        if ($this->canManagePosts($user)) {
            return true;
        }

        return (bool) ($post->is_published ?? false);
    }

    public function create(User $user): bool
    {
        return $this->canManagePosts($user);
    }

    public function update(User $user, Post $post): bool
    {
        return $this->canManagePosts($user);
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->canManagePosts($user);
    }

    public function restore(User $user, Post $post): bool
    {
        return $this->canManagePosts($user);
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $this->canManagePosts($user);
    }

    public function publish(User $user, Post $post): bool
    {
        return $this->canManagePosts($user);
    }

    protected function canManagePosts(User $user): bool
    {
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super_admin', 'admin', 'manager'])) {
            return true;
        }

        if (method_exists($user, 'can') && (
            $user->can('content.posts.viewAny') ||
            $user->can('content.posts.create') ||
            $user->can('content.posts.update') ||
            $user->can('content.posts.delete')
        )) {
            return true;
        }

        $legacyRole = strtolower((string) ($user->role ?? ''));

        return in_array($legacyRole, ['admin', 'superadmin', 'manager'], true);
    }
}