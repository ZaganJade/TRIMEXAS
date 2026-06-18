<?php

declare(strict_types=1);

namespace App\Services\Selection;

use App\Models\Rule;
use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * Menyusun data laporan audit PDF: narasi metodologi, input aktual,
 * fuzzifikasi, inferensi rule, dan defuzzifikasi berdasarkan snapshot batch.
 */
final class AuditReportBuilder
{
    private const CRITERION_LABELS = [
        'ipk' => 'IPK',
        'penghasilan' => 'Penghasilan Orang Tua',
        'prestasi_akademis' => 'Prestasi Akademis',
        'prestasi_non_akademis' => 'Prestasi Non-Akademis',
        'tanggungan' => 'Tanggungan Keluarga',
    ];

    private const CONSEQUENT_LABELS = [
        'layak' => 'Layak',
        'dipertimbangkan' => 'Dipertimbangkan',
        'tidak_layak' => 'Tidak Layak',
    ];

    /**
     * @param  Collection<int, \App\Models\SelectionRuleEvaluation>  $evaluations
     * @return array<string, mixed>
     */
    public function build(
        SelectionBatch $batch,
        Student $candidate,
        SelectionResult $result,
        Collection $evaluations,
    ): array {
        $snapshot = $result->input_snapshot ?? [];
        $thresholds = $batch->snapshot_thresholds ?? [];
        $threshold1 = (float) ($thresholds['threshold_1'] ?? 50);
        $threshold2 = (float) ($thresholds['threshold_2'] ?? 75);

        $evalByCode = $evaluations->keyBy('rule_code');
        $snapshotRules = collect($batch->snapshot_rules ?? []);
        $ruleCodes = $snapshotRules->pluck('code')->filter()->values();

        $descriptions = $ruleCodes->isEmpty()
            ? collect()
            : Rule::query()->whereIn('code', $ruleCodes)->pluck('description', 'code');

        $rules = $snapshotRules
            ->map(function (array $rule) use ($evalByCode, $descriptions): array {
                $eval = $evalByCode->get($rule['code']);
                $alpha = $eval ? (float) $eval->alpha : 0.0;

                return [
                    'code' => $rule['code'],
                    'antecedents' => $rule['antecedents'] ?? [],
                    'consequent' => $rule['consequent'] ?? null,
                    'description' => $descriptions[$rule['code']] ?? null,
                    'alpha' => $alpha,
                    'z' => $eval ? (float) $eval->z : 0.0,
                    'fired' => $alpha > 0,
                ];
            })
            ->sort(function (array $a, array $b): int {
                if ($a['fired'] !== $b['fired']) {
                    return $b['fired'] <=> $a['fired'];
                }

                if ($a['fired']) {
                    $alphaCompare = $b['alpha'] <=> $a['alpha'];

                    return $alphaCompare !== 0 ? $alphaCompare : strcmp($a['code'], $b['code']);
                }

                return strcmp($a['code'], $b['code']);
            })
            ->values();

        $firedRules = $rules->filter(fn (array $r) => $r['fired'])->values();
        $crispInputs = $this->buildCrispInputs($snapshot);
        $memberships = $this->buildMemberships($snapshot['memberships'] ?? []);
        $scoreBreakdown = $this->buildScoreBreakdown($firedRules, (float) ($result->score ?? 0));
        $categoryLabel = self::CONSEQUENT_LABELS[$result->category ?? ''] ?? ($result->category ?? '—');

        return [
            'candidate' => [
                'full_name' => $candidate->full_name,
                'nim' => $candidate->nim,
            ],
            'batch' => [
                'id' => $batch->id,
                'label' => $batch->label,
                'started_at' => $batch->started_at,
            ],
            'result' => [
                'eligible' => (bool) $result->eligible,
                'score' => $result->score,
                'category' => $result->category,
                'category_label' => $categoryLabel,
                'rank' => $result->rank,
                'ineligibility_reasons' => $result->ineligibility_reasons ?? [],
            ],
            'thresholds' => [
                'threshold_1' => $threshold1,
                'threshold_2' => $threshold2,
            ],
            'executive_summary' => $this->buildExecutiveSummary(
                $candidate,
                $result,
                $categoryLabel,
                $threshold1,
                $threshold2,
                $firedRules->count(),
                $rules->count(),
            ),
            'methodology' => $this->buildMethodologyNarrative($threshold1, $threshold2),
            'eligibility_narrative' => $this->buildEligibilityNarrative($result),
            'crisp_inputs' => $crispInputs,
            'crisp_narrative' => $this->buildCrispNarrative($crispInputs),
            'memberships' => $memberships,
            'fuzzification_narrative' => $this->buildFuzzificationNarrative($memberships),
            'rules' => $rules->all(),
            'fired_rules' => $firedRules->all(),
            'inference_narrative' => $this->buildInferenceNarrative($firedRules, $rules->count()),
            'score_breakdown' => $scoreBreakdown,
            'defuzzification_narrative' => $this->buildDefuzzificationNarrative($scoreBreakdown, $categoryLabel, $threshold1, $threshold2),
            'verdict_narrative' => $this->buildVerdictNarrative($result, $categoryLabel, $threshold1, $threshold2),
        ];
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return list<array{key: string, label: string, value: string, raw: float|int, unit: string}>
     */
    private function buildCrispInputs(array $snapshot): array
    {
        $rows = [];

        if (array_key_exists('ipk', $snapshot)) {
            $rows[] = [
                'key' => 'ipk',
                'label' => self::CRITERION_LABELS['ipk'],
                'value' => number_format((float) $snapshot['ipk'], 2),
                'raw' => (float) $snapshot['ipk'],
                'unit' => '',
            ];
        }

        if (array_key_exists('penghasilan', $snapshot)) {
            $rows[] = [
                'key' => 'penghasilan',
                'label' => self::CRITERION_LABELS['penghasilan'],
                'value' => $this->formatCurrency((float) $snapshot['penghasilan']),
                'raw' => (float) $snapshot['penghasilan'],
                'unit' => '',
            ];
        }

        if (array_key_exists('prestasi_akademis', $snapshot)) {
            $rows[] = [
                'key' => 'prestasi_akademis',
                'label' => self::CRITERION_LABELS['prestasi_akademis'],
                'value' => (string) $snapshot['prestasi_akademis'],
                'raw' => (float) $snapshot['prestasi_akademis'],
                'unit' => ' poin',
            ];
        }

        if (array_key_exists('prestasi_non_akademis', $snapshot)) {
            $rows[] = [
                'key' => 'prestasi_non_akademis',
                'label' => self::CRITERION_LABELS['prestasi_non_akademis'],
                'value' => (string) $snapshot['prestasi_non_akademis'],
                'raw' => (float) $snapshot['prestasi_non_akademis'],
                'unit' => ' poin',
            ];
        }

        if (array_key_exists('tanggungan', $snapshot)) {
            $rows[] = [
                'key' => 'tanggungan',
                'label' => self::CRITERION_LABELS['tanggungan'],
                'value' => (string) $snapshot['tanggungan'],
                'raw' => (float) $snapshot['tanggungan'],
                'unit' => ' orang',
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, array<string, float>>  $membershipData
     * @return list<array{criterion: string, label: string, entries: list<array{name: string, label: string, degree: float}>, dominant: ?array{name: string, label: string, degree: float}}>
     */
    private function buildMemberships(array $membershipData): array
    {
        $rows = [];

        foreach ($membershipData as $criterion => $sets) {
            $entries = collect($sets)
                ->map(fn (float $degree, string $name) => [
                    'name' => $name,
                    'label' => $this->labelize($name),
                    'degree' => $degree,
                ])
                ->sortByDesc('degree')
                ->values()
                ->all();

            $rows[] = [
                'criterion' => $criterion,
                'label' => self::CRITERION_LABELS[$criterion] ?? $criterion,
                'entries' => $entries,
                'dominant' => $entries[0] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $firedRules
     * @return array{numerator: float, denominator: float, terms: list<array{code: string, alpha: float, z: float, product: float}>, score: float}
     */
    private function buildScoreBreakdown(Collection $firedRules, float $score): array
    {
        $terms = [];
        $numerator = 0.0;
        $denominator = 0.0;

        foreach ($firedRules as $rule) {
            $product = $rule['alpha'] * $rule['z'];
            $numerator += $product;
            $denominator += $rule['alpha'];

            $terms[] = [
                'code' => $rule['code'],
                'alpha' => $rule['alpha'],
                'z' => $rule['z'],
                'product' => $product,
            ];
        }

        return [
            'numerator' => $numerator,
            'denominator' => $denominator,
            'terms' => $terms,
            'score' => $score,
        ];
    }

    private function buildExecutiveSummary(
        Student $candidate,
        SelectionResult $result,
        string $categoryLabel,
        float $threshold1,
        float $threshold2,
        int $firedCount,
        int $totalRules,
    ): string {
        if (! $result->eligible) {
            $reasons = implode('; ', $result->ineligibility_reasons ?? []);

            return sprintf(
                'Laporan ini mendokumentasikan hasil seleksi beasiswa untuk %s (NIM %s). '
                .'Kandidat tidak melanjutkan ke tahap perhitungan fuzzy karena tidak memenuhi prasyarat eligibility. '
                .'Alasan: %s.',
                $candidate->full_name,
                $candidate->nim,
                $reasons !== '' ? $reasons : 'tidak tercatat',
            );
        }

        $score = $result->score !== null ? number_format((float) $result->score, 2) : '—';
        $rank = $result->rank ? " dengan peringkat #{$result->rank}" : '';

        return sprintf(
            'Laporan ini mendokumentasikan hasil analisis fuzzy Tsukamoto untuk %s (NIM %s). '
            .'Berdasarkan %d kriteria input, sistem mengeksekusi %d rule dari %d rule dalam snapshot batch, '
            .'menghasilkan skor akhir Z = %s dan kategori "%s"%s. '
            .'Batas kategori: T₁ = %.0f (tidak layak / dipertimbangkan) dan T₂ = %.0f (dipertimbangkan / layak).',
            $candidate->full_name,
            $candidate->nim,
            5,
            $firedCount,
            $totalRules,
            $score,
            $categoryLabel,
            $rank,
            $threshold1,
            $threshold2,
        );
    }

    private function buildMethodologyNarrative(float $threshold1, float $threshold2): array
    {
        return [
            'overview' => 'Sistem Trimexas menggunakan logika fuzzy metode Tsukamoto untuk menilai kelayakan beasiswa. '
                .'Proses berjalan dalam empat tahap berurutan: (1) pengecekan eligibility, (2) fuzzifikasi input crisp, '
                .'(3) inferensi rule base, dan (4) defuzzifikasi weighted average.',
            'steps' => [
                [
                    'title' => 'Tahap 1 — Eligibility Gate',
                    'body' => 'Sebelum masuk mesin fuzzy, kandidat harus lulus empat prasyarat: status mahasiswa aktif, '
                        .'semester ≤ 6, IPK ≥ 3.0, dan akun telah disetujui (approved). Kandidat yang gagal dihentikan di sini.',
                ],
                [
                    'title' => 'Tahap 2 — Fuzzifikasi',
                    'body' => 'Nilai crisp tiap kriteria (IPK, penghasilan orang tua, prestasi akademis, prestasi non-akademis, '
                        .'dan tanggungan keluarga) dikonversi menjadi derajat keanggotaan (μ) pada himpunan fuzzy '
                        .'(misalnya rendah, sedang, tinggi) menggunakan fungsi keanggotaan segitiga/linear dari snapshot batch.',
                ],
                [
                    'title' => 'Tahap 3 — Inferensi Tsukamoto',
                    'body' => 'Setiap rule dievaluasi: α (derajat aktivasi) = MIN(μ antecedent). '
                        .'Rule dengan α > 0 dianggap aktif. Nilai z per rule dihitung dari consequent dan threshold: '
                        .'layak → z = T₂ + α×(100−T₂); dipertimbangkan → z = T₁ + α×(T₂−T₁); '
                        .'tidak layak → z = T₁ − α×T₁.',
                ],
                [
                    'title' => 'Tahap 4 — Defuzzifikasi & Kategorisasi',
                    'body' => sprintf(
                        'Skor akhir Z = Σ(αᵢ×zᵢ) / Σ(αᵢ). Kategori ditentukan: Z < %.0f → Tidak Layak; '
                        .'%.0f ≤ Z < %.0f → Dipertimbangkan; Z ≥ %.0f → Layak.',
                        $threshold1,
                        $threshold1,
                        $threshold2,
                        $threshold2,
                    ),
                ],
            ],
        ];
    }

    private function buildEligibilityNarrative(SelectionResult $result): ?string
    {
        if ($result->eligible) {
            return null;
        }

        $reasons = $result->ineligibility_reasons ?? [];

        if ($reasons === []) {
            return 'Kandidat tidak memenuhi syarat seleksi, namun alasan spesifik tidak tercatat dalam database.';
        }

        $list = implode('; ', array_map(
            fn (string $r) => "• {$r}",
            $reasons,
        ));

        return 'Kandidat tidak melanjutkan ke perhitungan fuzzy karena gagal prasyarat eligibility. '
            ."Alasan yang tercatat: {$list}. "
            .'Tanpa lulus gate ini, fuzzifikasi dan inferensi rule tidak dijalankan.';
    }

    /**
     * @param  list<array{key: string, label: string, value: string, raw: float|int, unit: string}>  $crispInputs
     */
    private function buildCrispNarrative(array $crispInputs): string
    {
        if ($crispInputs === []) {
            return 'Data input crisp tidak tersedia dalam snapshot hasil seleksi.';
        }

        $parts = array_map(
            fn (array $row) => sprintf('%s = %s%s', $row['label'], $row['value'], $row['unit']),
            $crispInputs,
        );

        return 'Nilai berikut diambil dari profil mahasiswa dan agregasi prestasi pada saat batch dijalankan: '
            .implode('; ', $parts).'. '
            .'Kelima kriteria ini menjadi masukan langsung ke proses fuzzifikasi.';
    }

    /**
     * @param  list<array{criterion: string, label: string, entries: list<array{name: string, label: string, degree: float}>, dominant: ?array{name: string, label: string, degree: float}}>  $memberships
     */
    private function buildFuzzificationNarrative(array $memberships): string
    {
        if ($memberships === []) {
            return 'Data derajat keanggotaan tidak tersedia. Fuzzifikasi mungkin tidak terekam dalam snapshot.';
        }

        $dominantParts = [];

        foreach ($memberships as $row) {
            if ($row['dominant'] === null) {
                continue;
            }

            $dominantParts[] = sprintf(
                '%s dominan pada himpunan "%s" (μ = %.3f)',
                $row['label'],
                $row['dominant']['label'],
                $row['dominant']['degree'],
            );
        }

        return 'Setiap nilai crisp dipetakan ke himpunan fuzzy menggunakan fungsi keanggotaan dari snapshot batch. '
            .'Derajat keanggotaan tertinggi (μ) menunjukkan seberapa kuat kriteria tersebut masuk kategori tertentu. '
            .'Hasil dominan: '.implode('; ', $dominantParts).'. '
            .'Nilai μ inilah yang dipakai sebagai antecedent pada evaluasi rule.';
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $firedRules
     */
    private function buildInferenceNarrative(Collection $firedRules, int $totalRules): string
    {
        if ($firedRules->isEmpty()) {
            return sprintf(
                'Dari %d rule dalam snapshot, tidak ada rule yang aktif (α > 0). '
                .'Ini berarti tidak ada kombinasi antecedent yang terpenuhi, sehingga skor defuzzifikasi menjadi 0.',
                $totalRules,
            );
        }

        $topRules = $firedRules->take(3)->map(function (array $rule): string {
            $antecedentText = $this->formatAntecedents($rule['antecedents'] ?? []);
            $consequent = self::CONSEQUENT_LABELS[$rule['consequent'] ?? ''] ?? $rule['consequent'];

            return sprintf(
                '%s (α=%.3f, z=%.2f, consequent: %s) — kondisi: %s',
                $rule['code'],
                $rule['alpha'],
                $rule['z'],
                $consequent,
                $antecedentText,
            );
        })->implode('; ');

        return sprintf(
            'Dari %d rule dalam snapshot, %d rule aktif (α > 0) dan berkontribusi ke skor akhir. '
            .'Rule aktif dipilih karena semua antecedent-nya terpenuhi; α dihitung sebagai MIN derajat keanggotaan antecedent. '
            .'Rule dengan kontribusi terbesar: %s.',
            $totalRules,
            $firedRules->count(),
            $topRules,
        );
    }

    /**
     * @param  array{numerator: float, denominator: float, terms: list<array{code: string, alpha: float, z: float, product: float}>, score: float}  $breakdown
     */
    private function buildDefuzzificationNarrative(array $breakdown, string $categoryLabel, float $threshold1, float $threshold2): string
    {
        if ($breakdown['denominator'] <= 0) {
            return 'Tidak ada rule aktif sehingga Σ(αᵢ) = 0. Skor defuzzifikasi diset ke 0 (tidak layak).';
        }

        $termParts = array_map(
            fn (array $t) => sprintf('(%s: %.3f×%.2f=%.2f)', $t['code'], $t['alpha'], $t['z'], $t['product']),
            $breakdown['terms'],
        );

        return sprintf(
            'Skor akhir dihitung dengan weighted average Tsukamoto: Z = Σ(αᵢ×zᵢ) / Σ(αᵢ). '
            .'Kontribusi per rule: %s. '
            .'Σ(αᵢ×zᵢ) = %.2f, Σ(αᵢ) = %.3f, sehingga Z = %.2f. '
            .'Dibandingkan threshold T₁=%.0f dan T₂=%.0f, skor ini masuk kategori "%s".',
            implode(' + ', $termParts),
            $breakdown['numerator'],
            $breakdown['denominator'],
            $breakdown['score'],
            $threshold1,
            $threshold2,
            $categoryLabel,
        );
    }

    private function buildVerdictNarrative(
        SelectionResult $result,
        string $categoryLabel,
        float $threshold1,
        float $threshold2,
    ): string {
        if (! $result->eligible) {
            return 'Kesimpulan: kandidat TIDAK LAYAK mengikuti seleksi beasiswa pada batch ini karena gagal prasyarat eligibility.';
        }

        $score = $result->score !== null ? number_format((float) $result->score, 2) : '—';
        $rank = $result->rank ? " Peringkat #{$result->rank} dalam batch." : '';

        $zoneExplanation = match ($result->category) {
            'layak' => sprintf('Skor berada di atas T₂ (%.0f), menandakan profil sangat sesuai kriteria penerima beasiswa.', $threshold2),
            'dipertimbangkan' => sprintf(
                'Skor berada antara T₁ (%.0f) dan T₂ (%.0f), menandakan profil perlu pertimbangan lebih lanjut.',
                $threshold1,
                $threshold2,
            ),
            default => sprintf('Skor berada di bawah T₁ (%.0f), menandakan profil belum memenuhi standar minimum.', $threshold1),
        };

        return sprintf(
            'Kesimpulan: kandidat dikategorikan "%s" dengan skor Z = %s.%s %s',
            $categoryLabel,
            $score,
            $rank,
            $zoneExplanation,
        );
    }

    /**
     * @param  array<string, string>  $antecedents
     */
    private function formatAntecedents(array $antecedents): string
    {
        $parts = [];

        foreach ($antecedents as $criterion => $setName) {
            $criterionLabel = self::CRITERION_LABELS[$criterion] ?? $criterion;
            $parts[] = sprintf('%s %s', $criterionLabel, $this->labelize($setName));
        }

        return implode(' DAN ', $parts);
    }

    private function labelize(string $value): string
    {
        return str_replace('_', ' ', $value);
    }

    private function formatCurrency(float $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }
}
