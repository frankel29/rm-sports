<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\RolUsuario;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('email')
                    ->label('Correo')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText('Deje en blanco para no cambiar la contraseña.'),
                Select::make('rol')
                    ->label('Rol')
                    ->options(RolUsuario::class)
                    ->default('VENDEDOR')
                    ->required(),
            ]);
    }
}
