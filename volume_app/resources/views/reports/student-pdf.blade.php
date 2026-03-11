<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório - {{ $student->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 18px; text-align: center; }
        .meta { text-align: center; color: #666; margin-bottom: 20px; }
        .student-info { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f0f0f0; font-size: 10px; text-transform: uppercase; }
    </style>
</head>
<body>
    <h1>AlunoBem - Relatório por Estudante</h1>
    <p class="meta">Gerado em: {{ $generated_at }}</p>
    <div class="student-info">
        <strong>{{ $student->name }}</strong> | Matrícula: {{ $student->enrollment_number }} | {{ $student->course }} - {{ $student->class_name }} | Total: {{ $total }}
    </div>
    <table>
        <thead><tr><th>Data/Hora</th><th>Método</th><th>Operador</th><th>Motivo</th></tr></thead>
        <tbody>
            @foreach($meals as $meal)
            <tr>
                <td>{{ $meal->served_at->format('d/m/Y H:i') }}</td>
                <td>{{ $meal->method === 'biometric' ? 'Biometria' : 'Manual' }}</td>
                <td>{{ $meal->operator->name ?? 'N/A' }}</td>
                <td>{{ $meal->manual_reason ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
