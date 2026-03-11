<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Exceções</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 18px; text-align: center; }
        .meta { text-align: center; color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f0f0f0; font-size: 10px; text-transform: uppercase; }
    </style>
</head>
<body>
    <h1>AlunoBem - Relatório de Exceções</h1>
    <p class="meta">Período: {{ $start_date }} a {{ $end_date }} | Total: {{ $total }} | Gerado em: {{ $generated_at }}</p>
    <table>
        <thead><tr><th>Data/Hora</th><th>Aluno</th><th>Matrícula</th><th>Operador</th><th>Motivo</th></tr></thead>
        <tbody>
            @foreach($meals as $meal)
            <tr>
                <td>{{ $meal->served_at->format('d/m/Y H:i') }}</td>
                <td>{{ $meal->student->name ?? 'N/A' }}</td>
                <td>{{ $meal->student->enrollment_number ?? 'N/A' }}</td>
                <td>{{ $meal->operator->name ?? 'N/A' }}</td>
                <td>{{ $meal->manual_reason ?? 'Não informado' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
