<?php

namespace App\Http\Requests\Mahasiswa;

use App\Domain\Achievement\AchievementScorer;
use App\Models\StudentAchievement;
use Illuminate\Foundation\Http\FormRequest;

class AchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isApprovedStudent() === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'in:akademis,non_akademis'],
            'level' => ['required', 'in:'.implode(',', AchievementScorer::levels())],
            'rank' => ['required', 'in:'.implode(',', AchievementScorer::ranks())],
            'year' => ['required', 'integer', 'min:2000', 'max:'.((int) date('Y') + 1)],
            'certificate' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'file',
                'mimes:pdf',
                'mimetypes:application/pdf',
                'max:5120', // 5MB
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            try {
                AchievementScorer::scoreFor((string) $this->input('level'), (string) $this->input('rank'));
            } catch (\InvalidArgumentException $e) {
                $validator->errors()->add('rank', 'Kombinasi level/peringkat tidak valid.');
            }

            // Cap 5 entries per student (only on store).
            if ($this->isMethod('post')) {
                $student = $this->user()->student;
                $count = $student
                    ? StudentAchievement::query()->where('student_id', $student->id)->count()
                    : 0;

                if ($count >= 5) {
                    $validator->errors()->add('title', 'Maksimal 5 entri prestasi.');
                }
            }
        });
    }
}
