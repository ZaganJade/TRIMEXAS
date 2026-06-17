<?php

namespace App\Actions\Selection;

use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use App\Models\User;
use App\Notifications\SelectionResultReadyNotification;

class NotifyStudentsOfBatchResultsAction
{
    public function execute(SelectionBatch $batch): void
    {
        SelectionResult::query()
            ->where('batch_id', $batch->id)
            ->with(['student.user'])
            ->orderBy('id')
            ->chunkById(100, function ($results) use ($batch): void {
                foreach ($results as $result) {
                    $user = $result->student?->user;

                    if (! $user instanceof User || ! $user->isApprovedStudent()) {
                        continue;
                    }

                    $user->notify(new SelectionResultReadyNotification($batch, $result));
                }
            });
    }
}
