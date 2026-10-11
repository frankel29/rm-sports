#!/usr/bin/env bash
# Crea un administrador GENERAL (sin línea asignada) en producción.
# Los admins de RM y GL se crean después desde el panel (Configuración → Usuarios).
# La contraseña se escribe aquí y nunca queda en el historial ni en archivos.
set -euo pipefail
cd /opt/rm-sports

read -r -p "Nombre: " NOMBRE
read -r -p "Correo: " CORREO
read -r -s -p "Contraseña nueva: " P; echo
read -r -s -p "Repite la contraseña: " P2; echo

[ "$P" = "$P2" ] || { echo "Las contraseñas no coinciden."; exit 1; }
[ "${#P}" -ge 10 ] || { echo "Usa al menos 10 caracteres."; exit 1; }

docker compose -f docker-compose.prod.yml exec -T \
  -e XDG_CONFIG_HOME=/tmp -e NOMBRE="$NOMBRE" -e CORREO="$CORREO" -e P="$P" \
  app php artisan tinker --execute='
    $u = App\Models\User::create([
        "name" => getenv("NOMBRE"),
        "email" => getenv("CORREO"),
        "password" => getenv("P"),
        "rol" => "ADMIN",
    ]);
    echo "Administrador general creado: {$u->email}" . PHP_EOL;
  '
