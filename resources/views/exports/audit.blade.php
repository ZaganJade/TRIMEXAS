<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Audit — {{ $report['candidate']['full_name'] }}</title>
    <style>
        @page { margin: 28px 32px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5pt;
            color: #1a2332;
            line-height: 1.55;
            margin: 0;
        }

        /* Header */
        .doc-header {
            border-bottom: 3px solid #1e4d8c;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .doc-brand {
            font-size: 8pt;
            color: #1e4d8c;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 0 0 4px 0;
        }
        .doc-title {
            font-size: 16pt;
            font-weight: bold;
            color: #0f1a2e;
            margin: 0 0 6px 0;
            line-height: 1.2;
        }
        .doc-subtitle {
            font-size: 10pt;
            color: #4a5568;
            margin: 0;
        }

        /* Meta table */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 9pt;
        }
        .meta-table td {
            padding: 5px 10px;
            border: 1px solid #d1d9e6;
            vertical-align: top;
        }
        .meta-table .label {
            background: #f0f4fa;
            font-weight: bold;
            width: 28%;
            color: #2d3748;
        }
        .meta-table .value { background: #fff; }

        /* Verdict box */
        .verdict-box {
            border: 2px solid #1e4d8c;
            background: #f0f6ff;
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .verdict-box.ineligible {
            border-color: #c53030;
            background: #fff5f5;
        }
        .verdict-score {
            font-size: 22pt;
            font-weight: bold;
            color: #1e4d8c;
            margin: 0;
            line-height: 1;
        }
        .verdict-box.ineligible .verdict-score { color: #c53030; }
        .verdict-category {
            display: inline-block;
            font-size: 9pt;
            font-weight: bold;
            padding: 3px 10px;
            border-radius: 3px;
            margin-top: 6px;
        }
        .cat-layak { background: #c6f6d5; color: #22543d; }
        .cat-dipertimbangkan { background: #fefcbf; color: #744210; }
        .cat-tidak_layak { background: #fed7d7; color: #742a2a; }

        /* Sections */
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1e4d8c;
            border-bottom: 1px solid #c5d3e8;
            padding-bottom: 4px;
            margin: 0 0 10px 0;
        }
        .section-num {
            display: inline-block;
            background: #1e4d8c;
            color: #fff;
            font-size: 8pt;
            width: 18px;
            height: 18px;
            text-align: center;
            line-height: 18px;
            border-radius: 9px;
            margin-right: 6px;
        }
        .narrative {
            background: #f8fafc;
            border-left: 3px solid #1e4d8c;
            padding: 10px 14px;
            margin-bottom: 12px;
            font-size: 9pt;
            color: #2d3748;
            text-align: justify;
        }
        .narrative.warning {
            border-left-color: #c53030;
            background: #fff5f5;
        }

        /* Methodology steps */
        .method-step { margin-bottom: 10px; }
        .method-step-title {
            font-weight: bold;
            font-size: 9.5pt;
            color: #2d3748;
            margin: 0 0 3px 0;
        }
        .method-step-body {
            font-size: 9pt;
            color: #4a5568;
            margin: 0;
            text-align: justify;
        }

        /* Data tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 12px;
        }
        table.data-table th {
            background: #1e4d8c;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #d1d9e6;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) td { background: #f8fafc; }
        table.data-table .right { text-align: right; }
        table.data-table .mono { font-family: DejaVu Sans Mono, monospace; }
        table.data-table .fired-row td { background: #ebf8ff !important; }
        table.data-table .idle-row td { color: #a0aec0; }

        /* Membership bars (text-based for PDF) */
        .mu-bar {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 8pt;
            color: #2b6cb0;
        }
        .mu-dominant { font-weight: bold; color: #1e4d8c; }

        /* Rule antecedent chips as text */
        .antecedent-text {
            font-size: 8pt;
            color: #4a5568;
        }

        /* Score formula */
        .formula-box {
            background: #edf2f7;
            border: 1px solid #cbd5e0;
            padding: 10px 14px;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 8.5pt;
            margin-bottom: 10px;
            border-radius: 3px;
        }

        /* Footer */
        .doc-footer {
            font-size: 7.5pt;
            color: #718096;
            border-top: 1px solid #d1d9e6;
            padding-top: 8px;
            margin-top: 24px;
            text-align: center;
        }

        .page-break { page-break-before: always; }
        .text-muted { color: #718096; font-size: 8.5pt; }
        .text-small { font-size: 8pt; }
        ul.reasons { margin: 6px 0 0 18px; padding: 0; }
        ul.reasons li { margin-bottom: 4px; font-size: 9pt; }
    </style>
</head>
<body>

{{-- ============================================================ --}}
{{-- HEADER --}}
{{-- ============================================================ --}}
<div class="doc-header">
    <p class="doc-brand">Trimexas — Sistem Seleksi Beasiswa</p>
    <h1 class="doc-title">Laporan Audit Analisis Fuzzy</h1>
    <p class="doc-subtitle">{{ $report['candidate']['full_name'] }} · NIM {{ $report['candidate']['nim'] }}</p>
</div>

{{-- Meta info --}}
<table class="meta-table">
    <tr>
        <td class="label">Kandidat</td>
        <td class="value">{{ $report['candidate']['full_name'] }} ({{ $report['candidate']['nim'] }})</td>
        <td class="label">Batch Seleksi</td>
        <td class="value">{{ $report['batch']['label'] }} (#{{ $report['batch']['id'] }})</td>
    </tr>
    <tr>
        <td class="label">Dijalankan</td>
        <td class="value">{{ optional($report['batch']['started_at'])->format('d M Y, H:i') ?? '—' }} WIB</td>
        <td class="label">Dicetak</td>
        <td class="value">{{ $now->format('d M Y, H:i') }} WIB</td>
    </tr>
    <tr>
        <td class="label">Threshold T₁</td>
        <td class="value">{{ number_format($report['thresholds']['threshold_1'], 0) }} (batas tidak layak / dipertimbangkan)</td>
        <td class="label">Threshold T₂</td>
        <td class="value">{{ number_format($report['thresholds']['threshold_2'], 0) }} (batas dipertimbangkan / layak)</td>
    </tr>
</table>

{{-- ============================================================ --}}
{{-- RINGKASAN EKSEKUTIF --}}
{{-- ============================================================ --}}
<div class="section">
    <h2 class="section-title"><span class="section-num">1</span> Ringkasan Eksekutif</h2>
    <div class="narrative">{{ $report['executive_summary'] }}</div>

    @if ($report['result']['eligible'])
        <div class="verdict-box">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="40%" valign="middle">
                        <p class="verdict-score">{{ $report['result']['score'] !== null ? number_format((float) $report['result']['score'], 2) : '—' }}</p>
                        <p class="text-small text-muted" style="margin:4px 0 0 0;">Skor Akhir (Z) · Metode Tsukamoto</p>
                    </td>
                    <td width="60%" valign="middle">
                        @php $catClass = 'cat-' . ($report['result']['category'] ?? 'tidak_layak'); @endphp
                        <span class="verdict-category {{ $catClass }}">{{ $report['result']['category_label'] }}</span>
                        @if ($report['result']['rank'])
                            <span class="text-small" style="margin-left:8px;">Peringkat #{{ $report['result']['rank'] }}</span>
                        @endif
                        <p class="text-small" style="margin:8px 0 0 0;">{{ $report['verdict_narrative'] }}</p>
                    </td>
                </tr>
            </table>
        </div>
    @else
        <div class="verdict-box ineligible">
            <p class="verdict-score" style="font-size:14pt;">Tidak Memenuhi Syarat</p>
            <p class="text-small" style="margin:6px 0 0 0;">{{ $report['verdict_narrative'] }}</p>
        </div>
    @endif
</div>

{{-- ============================================================ --}}
{{-- METODOLOGI --}}
{{-- ============================================================ --}}
<div class="section">
    <h2 class="section-title"><span class="section-num">2</span> Metodologi Analisis</h2>
    <div class="narrative">{{ $report['methodology']['overview'] }}</div>

    @foreach ($report['methodology']['steps'] as $step)
        <div class="method-step">
            <p class="method-step-title">{{ $step['title'] }}</p>
            <p class="method-step-body">{{ $step['body'] }}</p>
        </div>
    @endforeach
</div>

{{-- ============================================================ --}}
{{-- ELIGIBILITY (jika tidak eligible) --}}
{{-- ============================================================ --}}
@if (! $report['result']['eligible'])
    <div class="section">
        <h2 class="section-title"><span class="section-num">3</span> Hasil Pengecekan Eligibility</h2>
        <div class="narrative warning">{{ $report['eligibility_narrative'] }}</div>
        @if (count($report['result']['ineligibility_reasons'] ?? []) > 0)
            <ul class="reasons">
                @foreach ($report['result']['ineligibility_reasons'] as $reason)
                    <li>{{ $reason }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@else

{{-- ============================================================ --}}
{{-- INPUT CRISP --}}
{{-- ============================================================ --}}
<div class="section">
    <h2 class="section-title"><span class="section-num">3</span> Input Data Kandidat (Crisp)</h2>
    <div class="narrative">{{ $report['crisp_narrative'] }}</div>

    @if (count($report['crisp_inputs']) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40%">Kriteria</th>
                    <th>Nilai Input</th>
                    <th style="width:20%">Satuan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['crisp_inputs'] as $input)
                    <tr>
                        <td>{{ $input['label'] }}</td>
                        <td class="mono">{{ $input['value'] }}</td>
                        <td class="text-muted">{{ trim($input['unit']) ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- ============================================================ --}}
{{-- FUZZIFIKASI --}}
{{-- ============================================================ --}}
<div class="section">
    <h2 class="section-title"><span class="section-num">4</span> Hasil Fuzzifikasi</h2>
    <div class="narrative">{{ $report['fuzzification_narrative'] }}</div>

    @if (count($report['memberships']) > 0)
        @foreach ($report['memberships'] as $membership)
            <p style="font-weight:bold; font-size:9.5pt; margin:12px 0 4px 0; color:#2d3748;">
                {{ $membership['label'] }}
                @if ($membership['dominant'])
                    <span class="text-muted" style="font-weight:normal;">
                        — dominan: <strong>{{ $membership['dominant']['label'] }}</strong>
                        (μ = {{ number_format($membership['dominant']['degree'], 3) }})
                    </span>
                @endif
            </p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Himpunan Fuzzy</th>
                        <th class="right" style="width:18%">Derajat μ</th>
                        <th style="width:35%">Visualisasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($membership['entries'] as $entry)
                        @php
                            $barLen = (int) round($entry['degree'] * 20);
                            $bar = str_repeat('█', $barLen) . str_repeat('░', 20 - $barLen);
                            $isDominant = $membership['dominant'] && $entry['name'] === $membership['dominant']['name'];
                        @endphp
                        <tr>
                            <td class="{{ $isDominant ? 'mu-dominant' : '' }}">{{ $entry['label'] }}</td>
                            <td class="right mono {{ $isDominant ? 'mu-dominant' : '' }}">{{ number_format($entry['degree'], 3) }}</td>
                            <td class="mu-bar">{{ $bar }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @else
        <p class="text-muted">Data fuzzifikasi tidak tersedia.</p>
    @endif
</div>

{{-- ============================================================ --}}
{{-- INFERENSI RULE --}}
{{-- ============================================================ --}}
<div class="section page-break">
    <h2 class="section-title"><span class="section-num">5</span> Inferensi Rule Base</h2>
    <div class="narrative">{{ $report['inference_narrative'] }}</div>

    <p class="text-small text-muted" style="margin-bottom:8px;">
        α = derajat aktivasi rule (MIN antecedent). z = nilai consequent hasil defuzzifikasi per rule.
        Hanya rule dengan α &gt; 0 yang berkontribusi ke skor akhir.
    </p>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:10%">Rule</th>
                <th style="width:12%">Consequent</th>
                <th class="right" style="width:8%">α</th>
                <th class="right" style="width:8%">z</th>
                <th>Antecedent (Kondisi IF)</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report['rules'] as $rule)
                @php
                    $consequentLabels = ['layak' => 'Layak', 'dipertimbangkan' => 'Dipertimbangkan', 'tidak_layak' => 'Tidak Layak'];
                    $consequentLabel = $consequentLabels[$rule['consequent'] ?? ''] ?? ($rule['consequent'] ?? '—');
                    $antecedentParts = [];
                    $criterionLabels = [
                        'ipk' => 'IPK', 'penghasilan' => 'Penghasilan', 'prestasi_akademis' => 'Prestasi Akad.',
                        'prestasi_non_akademis' => 'Prestasi Non-Akad.', 'tanggungan' => 'Tanggungan',
                    ];
                    foreach ($rule['antecedents'] ?? [] as $crit => $set) {
                        $antecedentParts[] = ($criterionLabels[$crit] ?? $crit) . ' ' . str_replace('_', ' ', $set);
                    }
                @endphp
                <tr class="{{ $rule['fired'] ? 'fired-row' : 'idle-row' }}">
                    <td class="mono" style="font-weight:bold;">{{ $rule['code'] }}</td>
                    <td>{{ $consequentLabel }}</td>
                    <td class="right mono">{{ number_format($rule['alpha'], 3) }}</td>
                    <td class="right mono">{{ number_format($rule['z'], 2) }}</td>
                    <td class="antecedent-text">{{ implode(' ∧ ', $antecedentParts) }}</td>
                    <td class="text-small">{{ $rule['description'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ============================================================ --}}
{{-- DEFUZZIFIKASI --}}
{{-- ============================================================ --}}
<div class="section">
    <h2 class="section-title"><span class="section-num">6</span> Defuzzifikasi &amp; Perhitungan Skor</h2>
    <div class="narrative">{{ $report['defuzzification_narrative'] }}</div>

    @if (count($report['score_breakdown']['terms'] ?? []) > 0)
        <div class="formula-box">
            Z = Σ(αᵢ × zᵢ) / Σ(αᵢ)
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Rule</th>
                    <th class="right">αᵢ</th>
                    <th class="right">zᵢ</th>
                    <th class="right">αᵢ × zᵢ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['score_breakdown']['terms'] as $term)
                    <tr>
                        <td class="mono">{{ $term['code'] }}</td>
                        <td class="right mono">{{ number_format($term['alpha'], 3) }}</td>
                        <td class="right mono">{{ number_format($term['z'], 2) }}</td>
                        <td class="right mono">{{ number_format($term['product'], 2) }}</td>
                    </tr>
                @endforeach
                <tr style="font-weight:bold; background:#edf2f7 !important;">
                    <td colspan="3" class="right">Σ(αᵢ × zᵢ) / Σ(αᵢ) =</td>
                    <td class="right mono" style="font-size:10pt; color:#1e4d8c;">
                        {{ number_format($report['score_breakdown']['score'], 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="data-table" style="margin-top:8px;">
            <tr>
                <td class="label" style="background:#f0f4fa; font-weight:bold; width:30%;">Σ(αᵢ × zᵢ)</td>
                <td class="mono">{{ number_format($report['score_breakdown']['numerator'], 2) }}</td>
                <td class="label" style="background:#f0f4fa; font-weight:bold; width:30%;">Σ(αᵢ)</td>
                <td class="mono">{{ number_format($report['score_breakdown']['denominator'], 3) }}</td>
            </tr>
        </table>
    @endif
</div>

{{-- ============================================================ --}}
{{-- KESIMPULAN --}}
{{-- ============================================================ --}}
<div class="section">
    <h2 class="section-title"><span class="section-num">7</span> Kesimpulan</h2>
    <div class="narrative">{{ $report['verdict_narrative'] }}</div>

    <table class="meta-table" style="margin-top:10px;">
        <tr>
            <td class="label">Skor Akhir (Z)</td>
            <td class="value mono" style="font-size:11pt; font-weight:bold; color:#1e4d8c;">
                {{ $report['result']['score'] !== null ? number_format((float) $report['result']['score'], 2) : '—' }}
            </td>
            <td class="label">Kategori</td>
            <td class="value" style="font-weight:bold;">{{ $report['result']['category_label'] }}</td>
        </tr>
        <tr>
            <td class="label">Rule Aktif</td>
            <td class="value">{{ count($report['fired_rules']) }} dari {{ count($report['rules']) }} rule</td>
            <td class="label">Peringkat</td>
            <td class="value">{{ $report['result']['rank'] ? '#' . $report['result']['rank'] : '—' }}</td>
        </tr>
    </table>
</div>

@endif

{{-- Footer --}}
<div class="doc-footer">
    Laporan audit ini dihasilkan otomatis dari snapshot batch #{{ $report['batch']['id'] }}
    ({{ $report['batch']['label'] }}) dan dapat diverifikasi terhadap database Trimexas.<br>
    © {{ $now->year }} Trimexas — Sistem Seleksi Beasiswa Berbasis Logika Fuzzy Tsukamoto
</div>

</body>
</html>
