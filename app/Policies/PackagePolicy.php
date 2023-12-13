<?php

namespace App\Policies;

use App\Models\Package;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PackagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return !empty($user->id);
    }

    public function view(User $user, Package $package): bool
    {
        return $user->packages->contains($package);
    }

    public function create(User $user): bool
    {
        return !empty($user->id);
    }

    public function update(User $user, Package $package): bool
    {
        return $user->packages->contains($package);
    }

    public function delete(User $user, Package $package): bool
    {
        return $user->packages->contains($package);
    }
}
