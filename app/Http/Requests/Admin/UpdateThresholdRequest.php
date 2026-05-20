<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThresholdRequest extends FormRequest
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
            'threshold_1' => ['required', 'numeric', 'min:0', 'max:100'],
            'threshold_2' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $t1 = (float) $this->input('threshold_1');
            $t2 = (float) $this->input('threshold_2');

            if ($t1 >= $t2) {
                $validator->errors()->add('threshold_1', 'threshold_1 harus < threshold_2.');
            }
        });
    }
}
