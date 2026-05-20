<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateThresholdRequest;
use App\Models\OutputThreshold;
use Illuminate\Http\RedirectResponse;

class ThresholdController extends Controller
{
    public function update(UpdateThresholdRequest $request): RedirectResponse
    {
        $threshold = OutputThreshold::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->firstOrFail();

        $threshold->fill([
            'threshold_1' => $request->validated('threshold_1'),
            'threshold_2' => $request->validated('threshold_2'),
        ])->save();

        return back()->with('success', 'Threshold output disimpan.');
    }
}
