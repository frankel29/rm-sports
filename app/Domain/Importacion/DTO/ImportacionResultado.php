<?php

namespace App\Domain\Importacion\DTO;

class ImportacionResultado
{
    /**
     * @param  array<int, string>  $errores  número de fila (según el Excel) => mensaje de error
     */
    public function __construct(
        public readonly string $hoja,
        public readonly int $filasValidas,
        public readonly int $filasConError,
        public readonly array $errores,
    ) {}

    public function conError(): bool
    {
        return $this->filasConError > 0;
    }
}
