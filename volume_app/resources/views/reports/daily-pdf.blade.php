<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório Diário - {{ $date }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 18px; text-align: center; }
        .meta { text-align: center; color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; font-size: 10px; text-transform: uppercase; }
        .manual { background: #fff8e1; }
        .stats { margin: 15px 0; }
        .stats span { display: inline-block; margin-right: 30px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>AlunoBem - Relatório Diário</h1>
    <p class="meta">Data: {{ $date }} | Gerado em: {{ $generated_at }}</p>

    <div class="stats">
        <span>Total: {{ $total }}</span>
        <span>Biometria: {{ $biometric }}</span>
        <span>Manual: {{ $manual }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Horário</th>
                <th>Aluno</th>
                <th>Matrícula</th>
                <th>Método</th>
                <th>Operador</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($meals as $meal)
            <tr class="{{ $meal->method === 'manual' ? 'manual' : '' }}">
                <td>{{ $meal->served_at->format('H:i:s') }}</td>
                <td>{{ $meal->student->name ?? 'N/A' }}</td>
                <td>{{ $meal->student->enrollment_number ?? 'N/A' }}</td>
                <td>{{ $meal->method === 'biometric' ? 'Biometria' : 'Manual' }}</td>
                <td>{{ $meal->operator->name ?? 'N/A' }}</td>
                <td>{{ $meal->manual_reason ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
