<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SelectionBatch;
use App\Models\Student;
use App\Services\Selection\AuditViewDataBuilder;
use Inertia\Inertia;
use Inertia\Response;

class SelectionAuditController extends Controller
{
    public function show(SelectionBatch $batch, Student $candidate, AuditViewDataBuilder $builder): Response
    {
        return Inertia::render('Admin/Selection/Audit', $builder->build($batch, $candidate));
    }
}
