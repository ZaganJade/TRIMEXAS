<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateFuzzySetRequest;
use App\Models\Criterion;
use App\Models\FuzzySet;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CriteriaController extends Controller
{
    public function index(): Response
    {
        $criteria = Criterion::query()
            ->with(['fuzzySets' => fn ($q) => $q->orderByRaw("CASE name WHEN 'rendah' THEN 1 WHEN 'sedikit' THEN 1 WHEN 'sedang' THEN 2 WHEN 'tinggi' THEN 3 WHEN 'banyak' THEN 3 ELSE 4 END")])
            ->orderBy('display_order')
            ->get()
            ->map(fn (Criterion $c) => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'unit' => $c->unit,
                'domain_min' => (float) $c->domain_min,
                'domain_max' => (float) $c->domain_max,
                'sets' => $c->fuzzySets->map(fn (FuzzySet $s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'shape' => $s->shape,
                    'a' => (float) $s->a,
                    'b' => (float) $s->b,
                    'c' => $s->c !== null ? (float) $s->c : null,
                ])->values(),
            ])
            ->values();

        return Inertia::render('Admin/Criteria/Index', [
            'criteria' => $criteria,
        ]);
    }

    public function update(UpdateFuzzySetRequest $request, FuzzySet $fuzzySet): RedirectResponse
    {
        $data = $request->validated();
        $fuzzySet->fill([
            'a' => $data['a'],
            'b' => $data['b'],
            'c' => $data['c'] ?? null,
        ])->save();

        return back()->with('success', "Parameter himpunan {$fuzzySet->name} disimpan.");
    }
}
