<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório para Pagamento</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 18px; text-align: center; }
        .meta { text-align: center; color: #666; margin-bottom: 20px; }
        .stats { margin: 15px 0; }
        .stats span { display: inline-block; margin-right: 30px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f0f0f0; font-size: 10px; text-transform: uppercase; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>AlunoBem - Relatório para Pagamento</h1>
    <p class="meta">Gerado em: {{ $generated_at }}</p>

    <div class="stats">
        <span>Períodos: {{ $total_periods }}</span>
        <span>Almoços: {{ $total_meals }}</span>
        <span>Total: R$ {{ number_format($total_amount, 2, ',', '.') }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Protocolo</th>
                <th>Período</th>
                <th class="text-right">Almoços</th>
                <th class="text-right">Valor Unit.</th>
                <th class="text-right">Valor Total</th>
                <th>Fiscal</th>
                <th>Validado em</th>
            </tr>
        </thead>
        <tbody>
            @foreach($validations as $validation)
            <tr>
                <td>{{ $validation->protocol_number }}</td>
                <td>{{ $validation->period_start->format('d/m/Y') }} - {{ $validation->period_end->format('d/m/Y') }}</td>
                <td class="text-right">{{ $validation->total_meals }}</td>
                <td class="text-right">R$ {{ number_format((float) $validation->meal_value, 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format((float) $validation->total_value, 2, ',', '.') }}</td>
                <td>{{ $validation->fiscal->name ?? 'N/A' }}</td>
                <td>{{ $validation->validated_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
