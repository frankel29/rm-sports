<div class="fi-ta-ctn overflow-x-auto">
    <table class="fi-ta-table w-full text-sm">
        <thead>
            <tr class="text-left border-b border-gray-200 dark:border-white/10">
                <th class="px-3 py-2">Fecha</th>
                <th class="px-3 py-2">Tipo</th>
                <th class="px-3 py-2 text-right">Cantidad</th>
                <th class="px-3 py-2 text-right">Saldo</th>
                <th class="px-3 py-2">Origen</th>
                <th class="px-3 py-2">Nota</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movimientos as $movimiento)
                <tr class="border-b border-gray-100 dark:border-white/5">
                    <td class="px-3 py-2">{{ $movimiento->fecha_hecho->format('d/m/Y') }}</td>
                    <td class="px-3 py-2">{{ $movimiento->tipo->getLabel() }}</td>
                    <td class="px-3 py-2 text-right {{ $movimiento->cantidad < 0 ? 'text-danger-600' : 'text-success-600' }}">
                        {{ $movimiento->cantidad }}
                    </td>
                    <td class="px-3 py-2 text-right font-semibold {{ $movimiento->saldo < 0 ? 'text-danger-600' : '' }}">
                        {{ $movimiento->saldo }}
                    </td>
                    <td class="px-3 py-2">{{ $movimiento->origen->getLabel() }}</td>
                    <td class="px-3 py-2">{{ $movimiento->nota }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-3 py-4 text-center text-gray-500">Sin movimientos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
