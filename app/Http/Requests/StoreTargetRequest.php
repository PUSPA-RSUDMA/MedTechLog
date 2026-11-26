<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PriorityLevel;
use App\Enums\TargetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTargetRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'work_period_id' => ['required', 'exists:work_periods,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'work_category_id' => ['required', 'exists:work_categories,id'],
            'owner_id' => ['required', 'exists:users,id'],
            'output_unit' => ['required', 'string', 'max:60'],
            'target_value' => ['required', 'numeric', 'min:0.01'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'priority' => ['required', Rule::enum(PriorityLevel::class)],
            'status' => ['required', Rule::enum(TargetStatus::class)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul target wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'target_value.min' => 'Nilai target harus lebih besar dari 0.',
        ];
    }
}
