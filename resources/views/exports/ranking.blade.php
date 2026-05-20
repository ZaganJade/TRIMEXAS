<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ranking — {{ $batch->label }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1F2937; }
        h1 { font-size: 14pt; margin: 0 0 4px 0; }
        .meta { font-size: 9pt; color: #6B7280; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #D1D5DB; padding: 4px 6px; }
        th { background: #F3F4F6; text-align: left; }
        .right { text-align: right; }
        .footer { font-size: 8pt; color: #6B7280; margin-top: 16px; border-top: 1px solid #D1D5DB; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>Trimexas — Ranking Beasiswa</h1>
    <div class="meta">
        Batch: <strong>{{ $batch->label }}</strong> · Snapshot ID #{{ $batch->id }} ·
        Dieksekusi {{ optional($batch->started_at)->format('d M Y H:i') ?: '-' }} ·
        Dicetak {{ $now->format('d M Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>NIM</th>
                <th class="right">Skor</th>
                <th>Kategori</th>
                <th>Eligible</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->rank ?? '-' }}</td>
                    <td>{{ $row->student?->full_name ?? '-' }}</td>
                    <td>{{ $row->student?->nim ?? '-' }}</td>
                    <td class="right">{{ $row->score !== null ? number_format((float) $row->score, 2) : '-' }}</td>
                    <td>{{ $row->category ?? '-' }}</td>
                    <td>{{ $row->eligible ? 'Ya' : 'Tidak' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Snapshot batch ID {{ $batch->id }}, dieksekusi {{ optional($batch->started_at)->toIso8601String() ?: '-' }}.
        Halaman ini dibuat untuk audit dan dapat diverifikasi terhadap database
        Trimexas. — © {{ $now->year }} Trimexas
    </div>
</body>
</html>
