<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Reservas</title>
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
    <h1>Reporte de Reservas</h1>
    <div class="meta">Generado: {{ $generatedAt }}</div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Cliente</th>
                <th>Mesa</th>
                <th>Cap.</th>
                <th>Personas</th>
                <th>Estado</th>
                <th>Monto</th>
                <th>Pagado</th>
                <th>Pago</th>
                <th>Notas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reservas as $reserva)
                <tr>
                    <td>{{ $reserva['fecha'] }}</td>
                    <td>{{ $reserva['hora'] }}</td>
                    <td>{{ $reserva['cliente'] }}</td>
                    <td>{{ $reserva['mesa'] }}</td>
                    <td>{{ $reserva['capacidad'] }}</td>
                    <td>{{ $reserva['personas'] }}</td>
                    <td>{{ $reserva['estado'] }}</td>
                    <td>Bs {{ $reserva['monto'] }}</td>
                    <td>Bs {{ $reserva['monto_pagado'] }}</td>
                    <td>{{ $reserva['tipo_pago'] }}</td>
                    <td>{{ $reserva['notas'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">No hay reservas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
