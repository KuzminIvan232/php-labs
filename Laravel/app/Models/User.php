<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Lab 5: an authenticatable account (JWT login), distinct from the Customer
 * model, which is a restaurant guest's business record and is not itself a
 * login. One of three roles: client, manager, admin.
 */
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_CLIENT = 'client';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN = 'admin';

    public const ROLES = [self::ROLE_CLIENT, self::ROLE_MANAGER, self::ROLE_ADMIN];

    /** Rank used to compare roles (higher = more access). Mirrors Symfony's role_hierarchy. */
    private const ROLE_RANK = [
        self::ROLE_CLIENT => 1,
        self::ROLE_MANAGER => 2,
        self::ROLE_ADMIN => 3,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * Note: 'role' is deliberately NOT fillable from arbitrary request input —
     * it is only ever set directly in code (AuthController forces 'client' on
     * public registration; UserController::updateRole is the only other
     * writer, and it is Admin-only), so nobody can grant themselves elevated
     * access by slipping a "role" key into a registration payload.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * True if this user's role is at or above the given role in the
     * client < manager < admin hierarchy.
     */
    public function hasAtLeastRole(string $role): bool
    {
        return (self::ROLE_RANK[$this->role] ?? 0) >= (self::ROLE_RANK[$role] ?? PHP_INT_MAX);
    }

    /**
     * Required by Tymon\JWTAuth\Contracts\JWTSubject: the value embedded in
     * the token's "sub" claim and used to re-fetch the user on each request.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Custom claims embedded in the JWT. Carrying the role in the token
     * itself (not just in the DB) lets the role middleware check access
     * without an extra query on every request.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return ['role' => $this->role];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
