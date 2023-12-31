<?php

namespace App\Models;

use App\Traits\Model\FirebaseNotifications;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, FirebaseNotifications;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function favorite(Package $package, array $meta = []): void
    {
        if (empty($meta)) {
            $meta['alias'] = $package->code;
        }

        $this->packages()->attach($package, $meta);
    }

    public function unfavorite(Package $package): void
    {
        $this->packages()->detach($package->id);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class)
            ->as('meta')
            ->withPivot(['alias','icon'])
            ->withTimestamps();
    }
}
