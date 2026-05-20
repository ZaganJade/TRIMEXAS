<?php

namespace App\Services\Selection;

use App\Models\SelectionBatch;

class BatchStateService
{
    public function isAnyBatchRunning(): bool
    {
        return SelectionBatch::query()
            ->whereIn('status', [SelectionBatch::STATUS_QUEUED, SelectionBatch::STATUS_RUNNING])
            ->exists();
    }
}
