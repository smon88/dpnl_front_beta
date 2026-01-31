<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'url',
        'description',
        'backend_uid',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    public function approvedUsers()
    {
        return $this->users()->wherePivot('status', 'approved');
    }

    public function pendingUsers()
    {
        return $this->users()->wherePivot('status', 'pending');
    }

    public function owner()
    {
        return $this->users()->wherePivot('role', 'owner')->first();
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
