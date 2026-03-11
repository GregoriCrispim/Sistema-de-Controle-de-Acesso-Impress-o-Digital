<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório Mensal - {{ $month_name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 18px; text-align: center; }
        .meta { text-align: center; color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f0f0f0; font-size: 10px; text-transform: uppercase; }
        .stats span { display: inline-block; margin-right: 30px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>AlunoBem - Relatório Mensal</h1>
    <p class="meta">{{ $month_name }} | Gerado em: {{ $generated_at }}</p>
    <div class="stats"><span>Total: {{ $total }}</span><span>Biometria: {{ $biometric }}</span><span>Manual: {{ $manual }}</span></div>
    <table>
        <thead><tr><th>Data</th><th style="text-align:right">Total</th><th style="text-align:right">Biometria</th><th style="text-align:right">Manual</th></tr></thead>
        <tbody>
            @foreach($daily_stats as $date => $stats)
            <tr>
                <td>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                <td style="text-align:right">{{ $stats['total'] }}</td>
                <td style="text-align:right">{{ $stats['biometric'] }}</td>
                <td style="text-align:right">{{ $stats['manual'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
