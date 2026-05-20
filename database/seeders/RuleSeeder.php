<?php

namespace Database\Seeders;

use App\Models\Rule;
use Illuminate\Database\Seeder;

/**
 * Seeder rule base — 75 rule sesuai docs/KnowledgeBase_RuleSpec.md §3.
 *
 * Format antecedents: [
 *   'ipk' => 'rendah'|'sedang'|'tinggi',
 *   'penghasilan' => 'rendah'|'sedang'|'tinggi',
 *   'prestasi_akademis' => 'sedikit'|'sedang'|'banyak',
 *   'prestasi_non_akademis' => 'sedikit'|'sedang'|'banyak',
 *   'tanggungan' => 'sedikit'|'sedang'|'banyak',
 * ]
 *
 * Hard-coded sesuai keputusan brainstorming Q9 = C (rule base tidak editable via UI).
 */
class RuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rules() as $rule) {
            Rule::updateOrCreate(
                ['code' => $rule['code']],
                [
                    'antecedents' => $rule['antecedents'],
                    'consequent' => $rule['consequent'],
                    'description' => $rule['description'],
                    'active' => true,
                ]
            );
        }
    }

    /**
     * @return list<array{code:string, antecedents:array<string,string>, consequent:string, description:string}>
     */
    private function rules(): array
    {
        $a = fn (string $ipk, string $hsl, string $pa, string $pna, string $tng): array => [
            'ipk' => $ipk,
            'penghasilan' => $hsl,
            'prestasi_akademis' => $pa,
            'prestasi_non_akademis' => $pna,
            'tanggungan' => $tng,
        ];

        $L = Rule::CONSEQUENT_LAYAK;
        $D = Rule::CONSEQUENT_DIPERTIMBANGKAN;
        $T = Rule::CONSEQUENT_TIDAK_LAYAK;

        return [
            // 3.1 LAYAK (R001 - R015)
            ['code' => 'R001', 'antecedents' => $a('tinggi', 'rendah', 'banyak', 'banyak', 'banyak'),  'consequent' => $L, 'description' => 'Kandidat ideal sempurna: prestasi tinggi + ekonomi sulit + tanggungan banyak'],
            ['code' => 'R002', 'antecedents' => $a('tinggi', 'rendah', 'banyak', 'banyak', 'sedang'),  'consequent' => $L, 'description' => 'Sangat layak: prestasi sangat baik, ekonomi rendah'],
            ['code' => 'R003', 'antecedents' => $a('tinggi', 'rendah', 'banyak', 'sedang', 'banyak'),  'consequent' => $L, 'description' => 'Akademis kuat + ekonomi sulit + tanggungan banyak'],
            ['code' => 'R004', 'antecedents' => $a('tinggi', 'rendah', 'banyak', 'sedang', 'sedang'),  'consequent' => $L, 'description' => 'Akademis kuat + ekonomi rendah'],
            ['code' => 'R005', 'antecedents' => $a('tinggi', 'rendah', 'banyak', 'sedikit', 'banyak'), 'consequent' => $L, 'description' => 'Akademis kuat dominan + ekonomi sulit + tanggungan banyak'],
            ['code' => 'R006', 'antecedents' => $a('tinggi', 'rendah', 'banyak', 'sedikit', 'sedang'), 'consequent' => $L, 'description' => 'Akademis kuat + ekonomi rendah'],
            ['code' => 'R007', 'antecedents' => $a('tinggi', 'rendah', 'sedang', 'banyak', 'banyak'),  'consequent' => $L, 'description' => 'Akademis solid + non-akademis tinggi + ekonomi sulit'],
            ['code' => 'R008', 'antecedents' => $a('tinggi', 'rendah', 'sedang', 'sedang', 'banyak'),  'consequent' => $L, 'description' => 'Profil seimbang + ekonomi sulit + tanggungan banyak'],
            ['code' => 'R009', 'antecedents' => $a('tinggi', 'sedang', 'banyak', 'banyak', 'banyak'),  'consequent' => $L, 'description' => 'Prestasi sangat baik + tanggungan banyak'],
            ['code' => 'R010', 'antecedents' => $a('tinggi', 'sedang', 'banyak', 'banyak', 'sedang'),  'consequent' => $L, 'description' => 'Prestasi sangat baik mengkompensasi ekonomi menengah'],
            ['code' => 'R011', 'antecedents' => $a('tinggi', 'sedang', 'banyak', 'sedang', 'banyak'),  'consequent' => $L, 'description' => 'Akademis kuat + tanggungan banyak'],
            ['code' => 'R012', 'antecedents' => $a('sedang', 'rendah', 'banyak', 'banyak', 'banyak'),  'consequent' => $L, 'description' => 'IPK cukup, prestasi sangat baik, ekonomi sulit'],
            ['code' => 'R013', 'antecedents' => $a('sedang', 'rendah', 'banyak', 'banyak', 'sedang'),  'consequent' => $L, 'description' => 'Prestasi tinggi mengkompensasi IPK sedang'],
            ['code' => 'R014', 'antecedents' => $a('sedang', 'rendah', 'banyak', 'sedang', 'banyak'),  'consequent' => $L, 'description' => 'Akademis kuat + ekonomi sulit + tanggungan banyak'],
            ['code' => 'R015', 'antecedents' => $a('tinggi', 'rendah', 'sedang', 'banyak', 'sedang'),  'consequent' => $L, 'description' => 'Akademis solid + non-akademis tinggi + ekonomi rendah'],

            // 3.2 DIPERTIMBANGKAN (R016 - R051)
            ['code' => 'R016', 'antecedents' => $a('tinggi', 'rendah', 'sedikit', 'banyak', 'banyak'),  'consequent' => $D, 'description' => 'Akademis kurang, non-akademis tinggi tidak bisa solo ke layak'],
            ['code' => 'R017', 'antecedents' => $a('tinggi', 'rendah', 'sedikit', 'banyak', 'sedang'),  'consequent' => $D, 'description' => 'IPK + non-akademis baik, akademis kurang'],
            ['code' => 'R018', 'antecedents' => $a('tinggi', 'rendah', 'sedikit', 'sedang', 'banyak'),  'consequent' => $D, 'description' => 'Profil moderat + ekonomi rendah'],
            ['code' => 'R019', 'antecedents' => $a('tinggi', 'rendah', 'sedikit', 'sedang', 'sedang'),  'consequent' => $D, 'description' => 'IPK tinggi tapi prestasi rendah'],
            ['code' => 'R020', 'antecedents' => $a('tinggi', 'rendah', 'sedikit', 'sedikit', 'banyak'), 'consequent' => $D, 'description' => 'Tanpa prestasi tapi IPK + ekonomi sangat mendukung'],
            ['code' => 'R021', 'antecedents' => $a('tinggi', 'rendah', 'sedikit', 'sedikit', 'sedang'), 'consequent' => $D, 'description' => 'Hanya ekonomi rendah + IPK tinggi'],
            ['code' => 'R022', 'antecedents' => $a('tinggi', 'sedang', 'sedang', 'banyak', 'banyak'),   'consequent' => $D, 'description' => 'Profil baik + tanggungan banyak'],
            ['code' => 'R023', 'antecedents' => $a('tinggi', 'sedang', 'sedang', 'banyak', 'sedang'),   'consequent' => $D, 'description' => 'Profil seimbang menengah'],
            ['code' => 'R024', 'antecedents' => $a('tinggi', 'sedang', 'sedang', 'sedang', 'banyak'),   'consequent' => $D, 'description' => 'Profil moderat + tanggungan'],
            ['code' => 'R025', 'antecedents' => $a('tinggi', 'sedang', 'sedang', 'sedang', 'sedang'),   'consequent' => $D, 'description' => 'Profil moderat sepenuhnya'],
            ['code' => 'R026', 'antecedents' => $a('tinggi', 'sedang', 'sedang', 'sedikit', 'banyak'),  'consequent' => $D, 'description' => 'Akademis cukup + tanggungan banyak'],
            ['code' => 'R027', 'antecedents' => $a('tinggi', 'sedang', 'sedikit', 'banyak', 'banyak'),  'consequent' => $D, 'description' => 'Non-akademis tinggi + tanggungan; akademis kurang'],
            ['code' => 'R028', 'antecedents' => $a('tinggi', 'sedang', 'sedikit', 'sedang', 'banyak'),  'consequent' => $D, 'description' => 'Profil sosial-ekonomi mendukung'],
            ['code' => 'R029', 'antecedents' => $a('tinggi', 'sedang', 'sedikit', 'sedikit', 'banyak'), 'consequent' => $D, 'description' => 'Tanggungan banyak tapi prestasi rendah'],
            ['code' => 'R030', 'antecedents' => $a('tinggi', 'sedang', 'banyak', 'sedikit', 'banyak'),  'consequent' => $D, 'description' => 'Akademis tinggi tapi non-akademis nol -> masih kuat di mid'],
            ['code' => 'R031', 'antecedents' => $a('tinggi', 'sedang', 'banyak', 'sedikit', 'sedang'),  'consequent' => $D, 'description' => 'IPK + akademis tinggi, non-akademis rendah'],
            ['code' => 'R032', 'antecedents' => $a('tinggi', 'sedang', 'banyak', 'sedang', 'sedang'),   'consequent' => $D, 'description' => 'Profil akademik baik + ekonomi menengah'],
            ['code' => 'R033', 'antecedents' => $a('tinggi', 'tinggi', 'banyak', 'banyak', 'banyak'),   'consequent' => $D, 'description' => 'Prestasi sangat tinggi tapi ekonomi tinggi -> masih dilirik'],
            ['code' => 'R034', 'antecedents' => $a('tinggi', 'tinggi', 'banyak', 'banyak', 'sedang'),   'consequent' => $D, 'description' => 'Prestasi tinggi mengkompensasi ekonomi tinggi parsial'],
            ['code' => 'R035', 'antecedents' => $a('tinggi', 'tinggi', 'banyak', 'sedang', 'banyak'),   'consequent' => $D, 'description' => 'Akademis kuat + tanggungan banyak'],
            ['code' => 'R036', 'antecedents' => $a('sedang', 'rendah', 'banyak', 'sedang', 'sedang'),   'consequent' => $D, 'description' => 'IPK menengah, prestasi cukup, ekonomi rendah'],
            ['code' => 'R037', 'antecedents' => $a('sedang', 'rendah', 'banyak', 'sedikit', 'banyak'),  'consequent' => $D, 'description' => 'Akademis kuat tapi IPK menengah'],
            ['code' => 'R038', 'antecedents' => $a('sedang', 'rendah', 'banyak', 'sedikit', 'sedang'),  'consequent' => $D, 'description' => 'Akademis kuat + ekonomi rendah, IPK menengah'],
            ['code' => 'R039', 'antecedents' => $a('sedang', 'rendah', 'sedang', 'banyak', 'banyak'),   'consequent' => $D, 'description' => 'Profil seimbang + ekonomi sulit + tanggungan banyak'],
            ['code' => 'R040', 'antecedents' => $a('sedang', 'rendah', 'sedang', 'banyak', 'sedang'),   'consequent' => $D, 'description' => 'Profil mid-range mendukung'],
            ['code' => 'R041', 'antecedents' => $a('sedang', 'rendah', 'sedang', 'sedang', 'banyak'),   'consequent' => $D, 'description' => 'Profil mid-range + ekonomi rendah'],
            ['code' => 'R042', 'antecedents' => $a('sedang', 'rendah', 'sedang', 'sedang', 'sedang'),   'consequent' => $D, 'description' => 'Profil sepenuhnya mid-range'],
            ['code' => 'R043', 'antecedents' => $a('sedang', 'rendah', 'sedang', 'sedikit', 'banyak'),  'consequent' => $D, 'description' => 'Akademis cukup + ekonomi mendukung'],
            ['code' => 'R044', 'antecedents' => $a('sedang', 'rendah', 'sedikit', 'banyak', 'banyak'),  'consequent' => $D, 'description' => 'Non-akademis tinggi tapi akademis kurang'],
            ['code' => 'R045', 'antecedents' => $a('sedang', 'rendah', 'sedikit', 'sedang', 'banyak'),  'consequent' => $D, 'description' => 'Sosial-ekonomi mendukung tapi prestasi tipis'],
            ['code' => 'R046', 'antecedents' => $a('sedang', 'rendah', 'sedikit', 'sedikit', 'banyak'), 'consequent' => $D, 'description' => 'Hanya ekonomi + tanggungan yang mendukung'],
            ['code' => 'R047', 'antecedents' => $a('sedang', 'sedang', 'banyak', 'banyak', 'banyak'),   'consequent' => $D, 'description' => 'Prestasi tinggi mengangkat IPK + ekonomi menengah'],
            ['code' => 'R048', 'antecedents' => $a('sedang', 'sedang', 'banyak', 'banyak', 'sedang'),   'consequent' => $D, 'description' => 'Profil prestasi tinggi mengkompensasi sebagian'],
            ['code' => 'R049', 'antecedents' => $a('sedang', 'sedang', 'banyak', 'sedang', 'banyak'),   'consequent' => $D, 'description' => 'Akademis tinggi + tanggungan banyak'],
            ['code' => 'R050', 'antecedents' => $a('sedang', 'sedang', 'sedang', 'banyak', 'banyak'),   'consequent' => $D, 'description' => 'Profil seimbang menengah'],
            ['code' => 'R051', 'antecedents' => $a('sedang', 'sedang', 'sedang', 'sedang', 'banyak'),   'consequent' => $D, 'description' => 'Profil mid-range dengan tanggungan banyak'],

            // 3.3 TIDAK LAYAK (R052 - R075)
            ['code' => 'R052', 'antecedents' => $a('sedang', 'tinggi', 'sedikit', 'sedikit', 'sedikit'), 'consequent' => $T, 'description' => 'Ekonomi mampu + prestasi rendah + tanggungan sedikit'],
            ['code' => 'R053', 'antecedents' => $a('sedang', 'tinggi', 'sedikit', 'sedikit', 'sedang'),  'consequent' => $T, 'description' => 'Ekonomi mampu + prestasi rendah'],
            ['code' => 'R054', 'antecedents' => $a('sedang', 'tinggi', 'sedikit', 'sedang', 'sedikit'),  'consequent' => $T, 'description' => 'Ekonomi mampu, akademis lemah'],
            ['code' => 'R055', 'antecedents' => $a('sedang', 'tinggi', 'sedang', 'sedikit', 'sedikit'),  'consequent' => $T, 'description' => 'Ekonomi mampu, profil moderat tipis'],
            ['code' => 'R056', 'antecedents' => $a('sedang', 'tinggi', 'sedang', 'sedang', 'sedikit'),   'consequent' => $T, 'description' => 'Ekonomi mampu, profil moderat'],
            ['code' => 'R057', 'antecedents' => $a('tinggi', 'tinggi', 'sedikit', 'sedikit', 'sedikit'), 'consequent' => $T, 'description' => 'IPK tinggi tapi tanpa kebutuhan finansial maupun prestasi'],
            ['code' => 'R058', 'antecedents' => $a('tinggi', 'tinggi', 'sedikit', 'sedikit', 'sedang'),  'consequent' => $T, 'description' => 'Tidak butuh dukungan finansial'],
            ['code' => 'R059', 'antecedents' => $a('tinggi', 'tinggi', 'sedikit', 'sedang', 'sedikit'),  'consequent' => $T, 'description' => 'Profil ekonomi mampu, prestasi tipis'],
            ['code' => 'R060', 'antecedents' => $a('tinggi', 'tinggi', 'sedang', 'sedikit', 'sedikit'),  'consequent' => $T, 'description' => 'Ekonomi mampu, akademis cukup, tidak butuh'],
            ['code' => 'R061', 'antecedents' => $a('tinggi', 'tinggi', 'sedang', 'sedang', 'sedikit'),   'consequent' => $T, 'description' => 'Ekonomi mampu, profil moderat'],
            ['code' => 'R062', 'antecedents' => $a('tinggi', 'tinggi', 'sedikit', 'banyak', 'sedikit'),  'consequent' => $T, 'description' => 'Non-akademis tinggi tidak cukup untuk angkat'],
            ['code' => 'R063', 'antecedents' => $a('sedang', 'sedang', 'sedikit', 'sedikit', 'sedikit'), 'consequent' => $T, 'description' => 'Profil tipis di semua dimensi'],
            ['code' => 'R064', 'antecedents' => $a('sedang', 'sedang', 'sedikit', 'sedikit', 'sedang'),  'consequent' => $T, 'description' => 'Profil tipis, indikasi tidak prioritas'],
            ['code' => 'R065', 'antecedents' => $a('sedang', 'sedang', 'sedikit', 'sedang', 'sedikit'),  'consequent' => $T, 'description' => 'Akademis lemah, non-akademis cukup, ekonomi tidak mendukung'],
            ['code' => 'R066', 'antecedents' => $a('sedang', 'sedang', 'sedang', 'sedikit', 'sedikit'),  'consequent' => $T, 'description' => 'Akademis cukup tapi tanpa indikator pendukung lain'],
            ['code' => 'R067', 'antecedents' => $a('tinggi', 'sedang', 'sedikit', 'sedikit', 'sedikit'), 'consequent' => $T, 'description' => 'IPK saja tidak cukup tanpa prestasi/sosial-ekonomi'],
            ['code' => 'R068', 'antecedents' => $a('tinggi', 'sedang', 'sedikit', 'sedikit', 'sedang'),  'consequent' => $T, 'description' => 'IPK + ekonomi menengah belum cukup'],
            ['code' => 'R069', 'antecedents' => $a('tinggi', 'sedang', 'sedikit', 'sedang', 'sedikit'),  'consequent' => $T, 'description' => 'IPK + non-akademis cukup tapi ekonomi mampu'],
            ['code' => 'R070', 'antecedents' => $a('tinggi', 'sedang', 'sedang', 'sedikit', 'sedikit'),  'consequent' => $T, 'description' => 'Profil cukup tapi tidak ada penguat'],
            ['code' => 'R071', 'antecedents' => $a('sedang', 'rendah', 'sedikit', 'sedikit', 'sedikit'), 'consequent' => $T, 'description' => 'Walaupun ekonomi rendah, profil prestasi nol'],
            ['code' => 'R072', 'antecedents' => $a('sedang', 'rendah', 'sedikit', 'sedikit', 'sedang'),  'consequent' => $T, 'description' => 'Ekonomi rendah saja tidak cukup'],
            ['code' => 'R073', 'antecedents' => $a('tinggi', 'tinggi', 'sedang', 'banyak', 'sedikit'),   'consequent' => $T, 'description' => 'Ekonomi tinggi sulit dikompensasi prestasi non-dominan'],
            ['code' => 'R074', 'antecedents' => $a('tinggi', 'tinggi', 'banyak', 'sedikit', 'sedikit'),  'consequent' => $T, 'description' => 'Ekonomi tinggi + tanggungan sedikit, akademis dominan tapi tidak butuh'],
            ['code' => 'R075', 'antecedents' => $a('sedang', 'tinggi', 'banyak', 'banyak', 'sedikit'),   'consequent' => $T, 'description' => 'Ekonomi mampu + prestasi tinggi tapi tanpa kebutuhan'],
        ];
    }
}
