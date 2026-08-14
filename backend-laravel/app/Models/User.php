<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'status',
        'is_super_admin',
        'google_id',
        'picture',
        'last_login_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'password'          => 'hashed',
        'is_super_admin'    => 'boolean',
    ];

    /**
     * Roles relationship (returns collection with 'slug' column).
     * Falls back gracefully if the roles table doesn't exist yet.
     */
    public function roles()
    {
        // Check if roles table exists to avoid crashing
        if (!\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            return collect(); // empty collection
        }
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }
}
