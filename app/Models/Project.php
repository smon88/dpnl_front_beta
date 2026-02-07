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


    public static function getStatuses(): array
    {
        return [
            "ACTIVE" => 'Activo',
            "INACTIVE" => 'Inactivo',
            "MAINTENANCE" => 'Mantenimiento',
        ];
    }

    public static function getStatusBadgeClass(string $status): string
    {
        return match($status) {
            "ACTIVE" => 'badge-success',
            "INACTIVE" => 'badge-danger',
            "MAINTENANCE" => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    public static function getStatusIcon(string $status): string
    {
        return match($status) {
            "ACTIVE" => 'fa-check',
            "INACTIVE" => 'fa-times',
            "MAINTENANCE" => 'fa-wrench',
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
        return $this->status === "ACTIVE";
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
