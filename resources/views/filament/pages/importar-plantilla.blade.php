<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="previsualizar" class="space-y-4">
            {{ $this->form }}

            <div class="flex gap-3">
                <x-filament::button type="submit">
                    Previsualizar
                </x-filament::button>

                @if ($resultados && ! $confirmado)
                    <x-filament::button color="success" wire:click="confirmar">
                        Confirmar importación
                    </x-filament::button>
                @endif
            </div>
        </form>
    </x-filament::section>

    @if ($resultados)
        <div class="space-y-4">
            @foreach ($resultados as $hoja => $resultado)
                <x-filament::section>
                    <x-slot name="heading">
                        {{ $hoja }}
                    </x-slot>

                    <div class="flex gap-4 text-sm mb-2">
                        <span class="text-success-600 font-medium">{{ $resultado->filasValidas }} fila(s) válidas</span>
                        <span class="{{ $resultado->conError() ? 'text-danger-600 font-medium' : 'text-gray-500' }}">
                            {{ $resultado->filasConError }} fila(s) con error
                        </span>
                    </div>

                    @if ($resultado->conError())
                        <ul class="list-disc list-inside text-sm text-danger-600 space-y-1">
                            @foreach ($resultado->errores as $numeroFila => $mensaje)
                                <li>Fila {{ $numeroFila }}: {{ $mensaje }}</li>
                            @endforeach
                        </ul>
                    @endif
                </x-filament::section>
            @endforeach
        </div>

        @if ($confirmado)
            <x-filament::section>
                <p class="text-success-600 font-medium">Datos guardados correctamente.</p>
            </x-filament::section>
        @endif
    @endif
</x-filament-panels::page>
