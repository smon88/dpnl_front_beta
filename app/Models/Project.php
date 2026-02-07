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
        'logo_url',
        'backend_uid',
        'status',
    ];

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_INACTIVE = 'INACTIVE';
    const STATUS_MAINTENANCE = 'MAINTENANCE';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
            self::STATUS_MAINTENANCE => 'Mantenimiento',
        ];
    }

    public static function getStatusBadgeClass(string $status): string
    {
        return match($status) {
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_INACTIVE => 'badge-danger',
            self::STATUS_MAINTENANCE => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    public static function getStatusIcon(string $status): string
    {
        return match($status) {
            self::STATUS_ACTIVE => 'fa-check',
            self::STATUS_INACTIVE => 'fa-times',
            self::STATUS_MAINTENANCE => 'fa-wrench',
            default => 'fa-question',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
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
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getLogoUrlAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }
        // Si ya es una URL completa, retornarla
        if (str_starts_with($value, 'http')) {
            return $value;
        }
        // Si es una ruta relativa, construir la URL
        return asset('storage/' . $value);
    }
}
