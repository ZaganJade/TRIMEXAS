<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
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
        $studentId = $this->route('student')?->id;

        return [
            'nim' => ['required', 'string', 'max:32', Rule::unique('students', 'nim')->ignore($studentId)],
            'full_name' => ['required', 'string', 'max:120'],
            'semester' => ['required', 'integer', 'min:1', 'max:14'],
            'status' => ['required', 'in:aktif,cuti,lulus,keluar'],
            'ipk' => ['required', 'numeric', 'min:0', 'max:4'],
            'penghasilan_ortu' => ['required', 'integer', 'min:0', 'max:50000000'],
            'tanggungan' => ['required', 'integer', 'min:0', 'max:8'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }
}
