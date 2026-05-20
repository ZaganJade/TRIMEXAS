<?php

namespace App\Http\Requests\Mahasiswa;

use App\Services\Selection\BatchStateService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOwnProfileRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:120'],
            'semester' => ['required', 'integer', 'min:1', 'max:14'],
            'ipk' => ['required', 'numeric', 'min:0', 'max:4'],
            'penghasilan_ortu' => ['required', 'integer', 'min:0', 'max:50000000'],
            'tanggungan' => ['required', 'integer', 'min:0', 'max:8'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Tolak HTTP 409 saat ada batch sedang running (FR profile-edit lock).
     */
    public function passedValidation(): void
    {
        if (app(BatchStateService::class)->isAnyBatchRunning()) {
            throw new HttpResponseException(
                response()->json([
                    'message' => 'Profil terkunci selama seleksi berjalan.',
                ], 409)
            );
        }
    }
}
