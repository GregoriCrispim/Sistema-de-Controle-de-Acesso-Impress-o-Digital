<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório por Operador</title>
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
    <h1>AlunoBem - Relatório por Operador</h1>
    <p class="meta">Período: {{ $start_date }} a {{ $end_date }} | Gerado em: {{ $generated_at }}</p>
    <table>
        <thead><tr><th>Operador</th><th style="text-align:right">Total</th><th style="text-align:right">Biometria</th><th style="text-align:right">Manual</th></tr></thead>
        <tbody>
            @foreach($operator_stats as $stat)
            <tr>
                <td>{{ $stat['operator']->name ?? 'N/A' }}</td>
                <td style="text-align:right">{{ $stat['total'] }}</td>
                <td style="text-align:right">{{ $stat['biometric'] }}</td>
                <td style="text-align:right">{{ $stat['manual'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
