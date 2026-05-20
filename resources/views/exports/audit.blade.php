<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Audit — {{ $candidate->full_name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1F2937; }
        h1 { font-size: 14pt; margin: 0 0 4px 0; }
        .meta { font-size: 9pt; color: #6B7280; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #D1D5DB; padding: 4px 6px; vertical-align: top; }
        th { background: #F3F4F6; text-align: left; }
        .right { text-align: right; }
        pre { font-family: DejaVu Sans Mono, monospace; font-size: 8pt; background: #F9FAFB; padding: 6px; }
        .footer { font-size: 8pt; color: #6B7280; margin-top: 16px; border-top: 1px solid #D1D5DB; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>Audit Trail — {{ $candidate->full_name }}</h1>
    <div class="meta">
        NIM: {{ $candidate->nim }} · Batch: {{ $batch->label }} (#{{ $batch->id }}) ·
        Skor: {{ $result->score !== null ? number_format((float) $result->score, 2) : '-' }} ·
        Kategori: {{ $result->category ?? '-' }} · Rank: {{ $result->rank ?? '-' }}
    </div>

    @if (! $result->eligible)
        <h2>Tidak Memenuhi Syarat</h2>
        <ul>
            @foreach (($result->ineligibility_reasons ?? []) as $reason)
                <li>{{ $reason }}</li>
            @endforeach
        </ul>
    @else
        <h2>Input Crisp</h2>
        <pre>{{ json_encode($result->input_snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

        <h2>Rule yang Dieksekusi</h2>
        <table>
            <thead>
                <tr>
                    <th>Rule</th>
                    <th>Consequent</th>
                    <th class="right">α</th>
                    <th class="right">z</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($evaluations as $e)
                    <tr>
                        <td>{{ $e->rule_code }}</td>
                        <td>{{ $e->consequent }}</td>
                        <td class="right">{{ number_format((float) $e->alpha, 4) }}</td>
                        <td class="right">{{ number_format((float) $e->z, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Snapshot batch ID {{ $batch->id }}, dieksekusi {{ optional($batch->started_at)->toIso8601String() ?: '-' }}.
        Audit ini dapat diverifikasi terhadap database Trimexas. — © {{ $now->year }} Trimexas
    </div>
</body>
</html>
