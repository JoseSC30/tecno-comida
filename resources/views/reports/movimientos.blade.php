<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Movimientos</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .meta { font-size: 12px; color: #374151; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: 700; }
        tr:nth-child(even) td { background: #f9fafb; }
    </style>
</head>
<body>
    <h1>Reporte de Movimientos de Inventario</h1>
    <div class="meta">Generado: {{ $generatedAt }}</div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Insumo</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Unidad</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movimientos as $movimiento)
                <tr>
                    <td>{{ $movimiento['fecha'] }}</td>
                    <td>{{ $movimiento['insumo'] }}</td>
                    <td>{{ $movimiento['tipo'] }}</td>
                    <td>{{ $movimiento['cantidad'] }}</td>
                    <td>{{ $movimiento['unidad'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No hay movimientos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
