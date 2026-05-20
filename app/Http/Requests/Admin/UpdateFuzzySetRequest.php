<?php

namespace App\Http\Requests\Admin;

use App\Models\FuzzySet;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFuzzySetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'a' => ['required', 'numeric'],
            'b' => ['required', 'numeric'],
            'c' => ['nullable', 'numeric'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var FuzzySet $set */
            $set = $this->route('fuzzySet');
            $criterion = $set->criterion;
            $a = (float) $this->input('a');
            $b = (float) $this->input('b');
            $c = $this->filled('c') ? (float) $this->input('c') : null;

            // Domain range check.
            $min = (float) $criterion->domain_min;
            $max = (float) $criterion->domain_max;

            foreach (['a' => $a, 'b' => $b] as $key => $value) {
                if ($value < $min || $value > $max) {
                    $validator->errors()->add(
                        $key,
                        "Nilai di luar domain kriteria ({$min} - {$max})."
                    );
                }
            }

            if ($c !== null && ($c < $min || $c > $max)) {
                $validator->errors()->add('c', "Nilai di luar domain kriteria ({$min} - {$max}).");
            }

            // Monotonicity per shape.
            switch ($set->shape) {
                case FuzzySet::SHAPE_LINEAR_TURUN:
                case FuzzySet::SHAPE_LINEAR_NAIK:
                    if ($a >= $b) {
                        $validator->errors()->add('a', 'Parameter harus monotonik: a < b.');
                    }
                    if ($c !== null) {
                        // c diabaikan untuk linear; tetap simpan tapi jangan diwajibkan monotonik.
                    }
                    break;

                case FuzzySet::SHAPE_SEGITIGA:
                    if ($c === null) {
                        $validator->errors()->add('c', 'Parameter c wajib untuk himpunan segitiga.');
                        break;
                    }
                    if (! ($a < $b && $b < $c)) {
                        $validator->errors()->add('a', 'Parameter harus monotonik: a < b < c.');
                    }
                    break;
            }
        });
    }
}
