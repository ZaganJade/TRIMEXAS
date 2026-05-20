<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use App\Models\SelectionRuleEvaluation;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function csv(SelectionBatch $batch): StreamedResponse
    {
        activity('export')
            ->causedBy(request()->user())
            ->performedOn($batch)
            ->withProperties(['format' => 'csv'])
            ->log("Export csv batch {$batch->id}");

        $filename = sprintf('ranking_batch_%d_%s.csv', $batch->id, now()->format('Ymd_His'));

        return new StreamedResponse(function () use ($batch): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF"); // UTF-8 BOM
            $csv = Writer::createFromStream($stream);
            $csv->insertOne(['rank', 'nama', 'nim', 'skor', 'kategori', 'eligible']);

            SelectionResult::query()
                ->where('batch_id', $batch->id)
                ->with('student:id,nim,full_name')
                ->orderBy('rank')
                ->orderByDesc('score')
                ->chunkById(200, function ($rows) use ($csv): void {
                    foreach ($rows as $row) {
                        $csv->insertOne([
                            $row->rank ?? '',
                            $row->student?->full_name ?? '',
                            $row->student?->nim ?? '',
                            $row->score !== null ? number_format((float) $row->score, 4, '.', '') : '',
                            $row->category ?? '',
                            $row->eligible ? '1' : '0',
                        ]);
                    }
                });
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    public function pdf(SelectionBatch $batch): Response
    {
        activity('export')
            ->causedBy(request()->user())
            ->performedOn($batch)
            ->withProperties(['format' => 'pdf'])
            ->log("Export pdf batch {$batch->id}");

        $rows = SelectionResult::query()
            ->where('batch_id', $batch->id)
            ->with('student:id,nim,full_name')
            ->orderBy('rank')
            ->orderByDesc('score')
            ->get();

        $pdf = Pdf::loadView('exports.ranking', [
            'batch' => $batch,
            'rows' => $rows,
            'now' => now(),
        ]);

        $filename = sprintf('ranking_batch_%d_%s.pdf', $batch->id, now()->format('Ymd_His'));

        return $pdf->download($filename);
    }

    public function auditPdf(SelectionBatch $batch, Student $candidate): Response
    {
        activity('export')
            ->causedBy(request()->user())
            ->performedOn($batch)
            ->withProperties(['format' => 'audit_pdf', 'candidate_id' => $candidate->id])
            ->log("Export audit pdf batch {$batch->id} candidate {$candidate->id}");

        $result = SelectionResult::query()
            ->where('batch_id', $batch->id)
            ->where('student_id', $candidate->id)
            ->firstOrFail();

        $evaluations = SelectionRuleEvaluation::query()
            ->where('batch_id', $batch->id)
            ->where('student_id', $candidate->id)
            ->orderByDesc('alpha')
            ->orderBy('rule_code')
            ->get();

        $pdf = Pdf::loadView('exports.audit', [
            'batch' => $batch,
            'candidate' => $candidate,
            'result' => $result,
            'evaluations' => $evaluations,
            'now' => now(),
        ]);

        $filename = sprintf('audit_batch_%d_%s_%s.pdf', $batch->id, $candidate->nim, now()->format('Ymd_His'));

        return $pdf->download($filename);
    }
}
