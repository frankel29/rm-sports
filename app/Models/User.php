<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Domain\Catalogo\Models\Responsable;
use App\Enums\RolUsuario;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'rol', 'responsable_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'rol' => RolUsuario::class,
        ];
    }

    /**
     * El backoffice es solo para administradores. VENDEDOR usará el POS y
     * TALLER el registro de lotes, pantallas que todavía no existen.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->esAdmin();
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    public function esAdmin(): bool
    {
        return $this->rol === RolUsuario::ADMIN;
    }

    /**
     * Administrador general: ve y edita todo, sin línea de negocio asignada.
     * Un admin de línea (RM o GL) es ADMIN pero no es "general".
     */
    public function esAdminGeneral(): bool
    {
        return $this->esAdmin() && $this->responsable_id === null;
    }
}
