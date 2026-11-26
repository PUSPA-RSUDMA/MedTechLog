<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ReviewDecision;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isSupervisor();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $needsComment = in_array(
            $this->input('decision'),
            [ReviewDecision::RevisionRequired->value, ReviewDecision::Rejected->value],
            true
        );

        return [
            'decision' => ['required', Rule::enum(ReviewDecision::class)],
            'comments' => [$needsComment ? 'required' : 'nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan verifikasi wajib dipilih.',
            'comments.required' => 'Catatan umpan balik wajib diisi saat meminta revisi atau menolak pekerjaan.',
        ];
    }
}
