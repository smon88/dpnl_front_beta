<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'alias',
        'tg_user',
        'role',
        'backend_uid',
        'tg_linked',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'tg_linked' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function hasTelegramLinked(): bool
    {
        return $this->tg_linked;
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    public function approvedProjects()
    {
        return $this->projects()->wherePivot('status', 'approved');
    }

    public function pendingProjects()
    {
        return $this->projects()->wherePivot('status', 'pending');
    }
}
